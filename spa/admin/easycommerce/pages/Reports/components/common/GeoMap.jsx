import React, { useEffect, useRef, useCallback, useState, useMemo } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import L from 'leaflet';
import * as topojson from 'topojson-client';
import 'leaflet/dist/leaflet.css';

const fixRing = (ring) => {
    const result = [ring[0]];
    for (let i = 1; i < ring.length; i++) {
        const prev = result[result.length - 1];
        let cur = [...ring[i]];
        while (cur[0] - prev[0] >  180) cur[0] -= 360;
        while (cur[0] - prev[0] < -180) cur[0] += 360;
        result.push(cur);
    }
    return result;
};

const fixGeometry = (geometry) => {
    if (!geometry) return geometry;
    if (geometry.type === 'Polygon') {
        return { ...geometry, coordinates: geometry.coordinates.map(fixRing) };
    }
    if (geometry.type === 'MultiPolygon') {
        return { ...geometry, coordinates: geometry.coordinates.map(p => p.map(fixRing)) };
    }
    return geometry;
};

const fixGeoJSON = (geojson) => ({
    ...geojson,
    features: geojson.features.map(f => ({ ...f, geometry: fixGeometry(f.geometry) })),
});

const getColor = (value, maxVal) => {
    if (!value || value === 0) return '#E5E7EB';
    const r = value / maxVal;
    if (r > 0.8) return '#1e3a5f';
    if (r > 0.6) return '#2563eb';
    if (r > 0.4) return '#3b82f6';
    if (r > 0.2) return '#93c5fd';
    return '#dbeafe';
};

const GeoMap = ({ endpoint, params = {}, data: propData = {}, isLoading: propLoading = false }) => {
    const mapRef        = useRef(null);
    const mapInstanceRef   = useRef(null);
    const geojsonLayerRef  = useRef(null);
    const isMountedRef     = useRef(true);
    const tipRef           = useRef(null);
    const highlightedLayerRef = useRef(null);

    const [data, setData] = useState(propData);
    const [isLoading, setIsLoading] = useState(propLoading || !!endpoint);

    const topLocations = data?.top_locations || [];
    const aggregate    = data?.aggregate || 'count';

    const paramsKey = useMemo(() => JSON.stringify(params), [params]);

    useEffect(() => {
        if (!endpoint) return;
        isMountedRef.current = true;
        setIsLoading(true);

        const fetchData = async () => {
            try {
                const result = await apiFetch({
                    path: addQueryArgs(endpoint, params),
                });
                if (isMountedRef.current && result.success && result.data) {
                    setData(result.data);
                }
            } catch (error) {
                console.error('Error fetching geo data:', error);
            } finally {
                if (isMountedRef.current) setIsLoading(false);
            }
        };

        fetchData();

        return () => { isMountedRef.current = false; };
    }, [endpoint, paramsKey]);

    useEffect(() => {
        if (!endpoint && propData && Object.keys(propData).length > 0) {
            setData(propData);
        }
    }, [propData, endpoint]);

    useEffect(() => {
        if (!endpoint) setIsLoading(propLoading);
    }, [propLoading, endpoint]);

    const showTip = useCallback((html, e) => {
        if (!tipRef.current) {
            const el = document.createElement('div');
            el.style.cssText = `
                position: absolute;
                background: white;
                border: 1px solid #ccc;
                border-radius: 6px;
                padding: 6px 10px;
                font-size: 13px;
                pointer-events: none;
                z-index: 9999;
                box-shadow: 0 2px 6px rgba(0,0,0,0.15);
                white-space: nowrap;
                display: none;
            `;
            if (mapRef.current) {
                mapRef.current.appendChild(el);
            }
            tipRef.current = el;
        }
        
        tipRef.current.innerHTML = html;
        tipRef.current.style.display = 'block';
        moveTip(e);
    }, []);

    const moveTip = (e) => {
        if (!tipRef.current || !mapRef.current) return;
        
        const mapRect = mapRef.current.getBoundingClientRect();
        const relX = e.originalEvent.clientX - mapRect.left;
        const relY = e.originalEvent.clientY - mapRect.top;
        
        // Hide if outside map bounds
        if (relX < 0 || relX > mapRect.width || relY < 0 || relY > mapRect.height) {
            tipRef.current.style.display = 'none';
            return;
        }
        
        tipRef.current.style.left = (relX + 14) + 'px';
        tipRef.current.style.top  = (relY - 10) + 'px';
    };

    const hideTip = useCallback(() => {
        if (tipRef.current) tipRef.current.style.display = 'none';
    }, []);

    useEffect(() => {
        isMountedRef.current = true;
        return () => {
            isMountedRef.current = false;
            if (tipRef.current) {
                tipRef.current.remove();
                tipRef.current = null;
            }
            if (mapInstanceRef.current) {
                mapInstanceRef.current.remove();
                mapInstanceRef.current = null;
            }
        };
    }, []);

    useEffect(() => {
        if (isLoading || !mapRef.current || !isMountedRef.current) return;

        const initMap = async () => {
            if (mapInstanceRef.current) {
                mapInstanceRef.current.remove();
                mapInstanceRef.current = null;
                geojsonLayerRef.current = null;
            }

            if (!isMountedRef.current) return;

            const map = L.map(mapRef.current, {
                center: [20, 0],
                zoom: 2,
                zoomSnap: 0.1,
                scrollWheelZoom: false,
                worldCopyJump: false,
                attributionControl: false,
            });

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd',
                maxZoom: 19,
                noWrap: true,
            }).addTo(map);

            mapInstanceRef.current = map;

            if (!isMountedRef.current) {
                map.remove();
                return;
            }

            if (data?.type === 'world') {
                await loadWorldData(map, data);
            } else if (data?.type === 'states' && data?.country) {
                await loadStateChoropleth(map, data);
            }
        };

        initMap();
    }, [data, isLoading]);

    const loadWorldData = async (map, data) => {
        const response = await fetch(EASYCOMMERCE.assets + 'common/lib/geo/countries-110m.json');
        const world    = await response.json();
        const raw      = topojson.feature(world, world.objects.countries);
        const countries = fixGeoJSON(raw);

        const orderData   = data?.data || {};
        const countryData = data?.country_data || {};
        const maxVal      = Math.max(1, ...Object.values(orderData).map(Number));

        const numericToIso2 = {};
        for (const [iso2, info] of Object.entries(countryData)) {
            if (info.numeric) {
                numericToIso2[info.numeric] = iso2;
            }
        }

        const getKey = (feature) => {
            const numericId = String(feature.id).padStart(3, '0');
            return numericToIso2[numericId] || '';
        };

        const style = (feature) => ({
            fillColor: getColor(orderData[getKey(feature)] || 0, maxVal),
            weight: 0.8,
            opacity: 1,
            color: '#fff',
            fillOpacity: 0.85,
        });

        const highlightStyle = {
            weight: 2,
            color: '#1e3a5f',
            fillOpacity: 1,
        };

        const onEachFeature = (feature, layer) => {
            const key   = getKey(feature);
            const count = orderData[key] || 0;
            const name  = countryData[key]?.name || feature.properties?.name || key;
            const html  = `<strong>${name}</strong><br/>${count > 0 ? countryData[key]?.display || `${count} order${count !== 1 ? 's' : ''}` : 'No orders'}`;

            layer.on({
                mouseover(e) {
                    if (highlightedLayerRef.current && highlightedLayerRef.current !== e.target) {
                        geojsonLayerRef.current?.resetStyle(highlightedLayerRef.current);
                    }
                    e.target.setStyle(highlightStyle);
                    e.target.bringToFront();
                    highlightedLayerRef.current = e.target;
                    showTip(html, e);
                },
                mousemove(e) { moveTip(e); },
                mouseout(e) {
                    if (highlightedLayerRef.current === e.target) {
                        geojsonLayerRef.current?.resetStyle(e.target);
                        highlightedLayerRef.current = null;
                    }
                    hideTip();
                },
            });
        };

        geojsonLayerRef.current = L.geoJSON(countries, { style, onEachFeature }).addTo(map);
        map.fitBounds([[-40, -170], [75, 180]], { padding: [0, 0] });
    };

    const loadStateChoropleth = async (map, data) => {
        const stateData = data?.data || {};
        const maxVal    = Math.max(1, ...Object.values(stateData).map(Number));

        // State-level polygon geojson is intentionally not bundled (the full set is
        // ~82MB) and is not fetched remotely to comply with WordPress.org guidelines.
        // State data is rendered as circle markers using coordinates from the API.
        loadStateCircles(map, data, maxVal);
    };

    const loadStateCircles = (map, data, maxVal) => {
        const stateData  = data?.data || {};
        const statesData = data?.states_data || [];
        const validStates = statesData.filter(s => s.latitude && s.longitude);

        if (!validStates.length) return;

        const stateDisplay = data?.states_display || {};
        const stateNameToCode = {};
        for (const code of Object.keys(stateDisplay)) {
            stateNameToCode[code.toUpperCase()] = code;
        }

        const stateDataKeys = Object.keys(stateData);
        const stateDataNameToCode = {};
        for (const key of stateDataKeys) {
            stateDataNameToCode[key.toUpperCase()] = key;
        }

        map.fitBounds(
            L.latLngBounds(validStates.map(s => [parseFloat(s.latitude), parseFloat(s.longitude)])),
            { padding: [30, 30] }
        );

        validStates.forEach(state => {
            const stateName = state.name?.toUpperCase() || '';
            const stateCode = state.state_code?.toUpperCase() || '';
            const key       = stateDataNameToCode[stateName] || stateDataNameToCode[stateCode] || stateNameToCode[stateName] || stateNameToCode[stateCode];
            const value = key ? stateData[key] : 0;
            const displayText = (stateDisplay[key]) || (aggregate === 'sum' ? `${Number(value).toLocaleString()}` : `${value} order${value !== 1 ? 's' : ''}`);
            const html  = `<strong>${state.name}</strong><br/>${value > 0 ? displayText : 'No orders'}`;

            const circle = L.circle(
                [parseFloat(state.latitude), parseFloat(state.longitude)],
                { color: 'white', weight: 1, fillColor: getColor(value, maxVal), fillOpacity: 0.85, radius: 60000 }
            ).addTo(map);

            circle.on({
                mouseover(e) { showTip(html, e); },
                mousemove(e) { moveTip(e); },
                mouseout()   { hideTip(); },
            });
        });
    };

    return (
        <div className="flex gap-6">
            <div className="w-[60%]">
                <div
                    ref={mapRef}
                    style={{ height: '480px', width: '100%', borderRadius: '8px', overflow: 'hidden' }}
                />
            </div>

            <div className="w-[40%]">
                <h4 className='text-[#3C3C42] font-semibold text-base mb-6'>Top Locations</h4>

                <div className="flex flex-col gap-3">
                    {topLocations.length > 0 ? topLocations.map((location, index) => (
                        <div key={index} className="flex justify-between p-4 rounded-xl bg-[#F8FAFC]">
                            <div className="flex gap-2">
                                <svg className='mt-1.5' width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13.3346 6.66732C13.3346 9.99598 9.64197 13.4627 8.40197 14.5333C8.28645 14.6202 8.14583 14.6672 8.0013 14.6672C7.85677 14.6672 7.71615 14.6202 7.60064 14.5333C6.36064 13.4627 2.66797 9.99598 2.66797 6.66732C2.66797 5.25283 3.22987 3.89628 4.23007 2.89608C5.23026 1.89589 6.58681 1.33398 8.0013 1.33398C9.41579 1.33398 10.7723 1.89589 11.7725 2.89608C12.7727 3.89628 13.3346 5.25283 13.3346 6.66732Z" stroke="#62748E" strokeWidth="1.33333" strokeLinecap="round" strokeLinejoin="round"/>
                                    <path d="M8 8.66602C9.10457 8.66602 10 7.77059 10 6.66602C10 5.56145 9.10457 4.66602 8 4.66602C6.89543 4.66602 6 5.56145 6 6.66602C6 7.77059 6.89543 8.66602 8 8.66602Z" stroke="#62748E" strokeWidth="1.33333" strokeLinecap="round" strokeLinejoin="round"/>
                                </svg>

                                <div className="">
                                    <h4 className='text-[#3C3C42] font-medium text-base'>{location.name}</h4>
                                    {data?.type === 'states' && <span className='text-[#45556C] text-xs'>{data.country_name}</span>}
                                </div>
                            </div>

                            <span className='text-[#0F172B] font-medium text-sm'>{location.display || Number(location.value).toLocaleString()}</span>
                        </div>
                    )) : (
                        <div className="text-[#62748E] text-sm p-4">No location data available</div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default GeoMap;

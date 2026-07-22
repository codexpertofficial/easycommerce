import React, { useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n';

const DualRangeSlider = ({ range, setRange, maxValue }) => {
    const sliderTrackRef = useRef(null);
    const sliderRef = useRef(null);

    useEffect(() => {
        updateSliderTrack();
    }, [range]);

    const updateSliderTrack = () => {
        const track = sliderTrackRef.current;
        if (track) {
            const minPercent = (range.min / maxValue) * 100;
            const maxPercent = (range.max / maxValue) * 100;
            track.style.left = `${minPercent}%`;
            track.style.right = `${100 - maxPercent}%`;
        }
    };

    const handleMinChange = (e) => {
        const value = Math.min(
            Math.round(Number(e.target.value)),
            range.max - 1
        );
        setRange((prev) => ({ ...prev, min: value }));
    };

    const handleMaxChange = (e) => {
        const value = Math.max(
            Math.round(Number(e.target.value)),
            range.min + 1
        );
        setRange((prev) => ({ ...prev, max: value }));
    };

    const handleDrag = (e, isMin) => {
        const slider = sliderRef.current;
        const rect = slider.getBoundingClientRect();
        const sliderWidth = rect.width;
        const offsetX = e.clientX - rect.left;

        const percent = Math.max(
            0,
            Math.min(100, (offsetX / sliderWidth) * 100)
        );
        const value = Math.round((percent / 100) * maxValue);

        if (isMin) {
            const newMin = Math.min(value, range.max - 1);
            setRange((prev) => ({ ...prev, min: newMin }));
        } else {
            const newMax = Math.max(value, range.min + 1);
            setRange((prev) => ({ ...prev, max: newMax }));
        }
    };

    return (
        <div className="p-4 bg-white rounded-md mt-4">
            <div className="relative h-2 bg-gray-200 rounded" ref={sliderRef}>
                <div
                    ref={sliderTrackRef}
                    className="absolute h-full bg-[#272435] rounded"
                />
                <div
                    className="absolute w-6 h-6 bg-white rounded-full cursor-pointer -top-2"
                    style={{ left: `${(range.min / maxValue) * 95}%`,
                            border: '4px solid #272435',
                    }}
                    onMouseDown={(e) => {
                        e.preventDefault();
                        const onMouseMove = (e) => handleDrag(e, true);
                        const onMouseUp = () => {
                            document.removeEventListener(
                                'mousemove',
                                onMouseMove
                            );
                            document.removeEventListener('mouseup', onMouseUp);
                        };
                        document.addEventListener('mousemove', onMouseMove);
                        document.addEventListener('mouseup', onMouseUp);
                    }}
                />
                <div
                    className="absolute w-6 h-6 bg-white rounded-full cursor-pointer -top-2"
                    style={{
                        left: `calc(${(range.max / maxValue) * 95}%)`,
                        border: '4px solid #272435',
                    }}
                    onMouseDown={(e) => {
                        e.preventDefault();
                        const onMouseMove = (e) => handleDrag(e, false);
                        const onMouseUp = () => {
                            document.removeEventListener(
                                'mousemove',
                                onMouseMove
                            );
                            document.removeEventListener('mouseup', onMouseUp);
                        };
                        document.addEventListener('mousemove', onMouseMove);
                        document.addEventListener('mouseup', onMouseUp);
                    }}
                />
            </div>
            <div className="flex justify-between mt-4 gap-10">
                <div className="flex flex-col">
                    <label className="text-[12px] mb-1 text-ec-placeholder">
                        {__('Min Price', 'easycommerce')}
                    </label>
                    <div className="relative">
                        <input
                            type="number"
                            value={range.min}
                            onChange={handleMinChange}
                            className="border border-ec-body rounded px-2 py-1 w-full pr-10"
                            min={0}
                            max={maxValue}
                        />
                    </div>
                </div>

                <div className="flex flex-col">
                    <label className="text-[12px] mb-1 text-ec-placeholder">
                        {__('Max Price', 'easycommerce')}
                    </label>
                    <div className="relative">
                        <input
                            type="number"
                            value={range.max}
                            onChange={handleMaxChange}
                            className="border border-ec-body rounded px-2 py-1 w-full pr-10"
                            min={0}
                            max={maxValue}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};

export default DualRangeSlider;

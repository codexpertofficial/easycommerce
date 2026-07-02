import { useState, useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n';
import { createPortal } from 'react-dom';

const units = {
  length : EASYCOMMERCE.units.length,
  weight : EASYCOMMERCE.units.weight,
  height : EASYCOMMERCE.units.length,
  width  : EASYCOMMERCE.units.length,
};

const Dimensions = ({ priceItem, setPriceItem }) => {
  const [heightUnit, setHeightUnit] = useState('in');
  const [widthUnit, setWidthUnit] = useState('in');
  const [lengthUnit, setLengthUnit] = useState('in');
  const [weightUnit, setWeightUnit] = useState('kg');

  const [heightDropDown, setHeightDropDown] = useState(false);
  const [widthDropDown, setWidthDropDown] = useState(false);
  const [lengthDropDown, setLengthDropDown] = useState(false);
  const [weightDropDown, setWeightDropDown] = useState(false);

  // Initialize units from API data
  useEffect(() => {
    const validWeightUnits = units.weight.map((unit) => unit.value);
    if (priceItem?.meta?.weight?.unit && validWeightUnits.includes(priceItem.meta.weight.unit)) {
      setWeightUnit(priceItem.meta.weight.unit);
    }
    if (priceItem?.meta?.height?.unit && units.height.map((unit) => unit.value).includes(priceItem.meta.height.unit)) {
      setHeightUnit(priceItem.meta.height.unit);
    }
    if (priceItem?.meta?.width?.unit && units.width.map((unit) => unit.value).includes(priceItem.meta.width.unit)) {
      setWidthUnit(priceItem.meta.width.unit);
    }
    if (priceItem?.meta?.length?.unit && units.length.map((unit) => unit.value).includes(priceItem.meta.length.unit)) {
      setLengthUnit(priceItem.meta.length.unit);
    }
  }, [priceItem]);

  // Dropdown position management
  const heightDropdownTriggerRef = useRef(null);
  const [heightDropdownPosition, setHeightDropdownPosition] = useState({
    top: 0,
    left: 0,
  });

  useEffect(() => {
    if (heightDropDown && heightDropdownTriggerRef.current) {
      const rect = heightDropdownTriggerRef.current.getBoundingClientRect();
      setHeightDropdownPosition({
        top: rect.bottom + 10 + window.scrollY,
        left: rect.left + window.scrollX,
      });
    }
  }, [heightDropDown]);

  const widthDropdownTriggerRef = useRef(null);
  const [widthDropdownPosition, setWidthDropdownPosition] = useState({
    top: 0,
    left: 0,
  });

  useEffect(() => {
    if (widthDropDown && widthDropdownTriggerRef.current) {
      const rect = widthDropdownTriggerRef.current.getBoundingClientRect();
      setWidthDropdownPosition({
        top: rect.bottom + 10 + window.scrollY,
        left: rect.left + window.scrollX,
      });
    }
  }, [widthDropDown]);

  const lengthDropdownTriggerRef = useRef(null);
  const [lengthDropdownPosition, setLengthDropdownPosition] = useState({
    top: 0,
    left: 0,
  });

  useEffect(() => {
    if (lengthDropDown && lengthDropdownTriggerRef.current) {
      const rect = lengthDropdownTriggerRef.current.getBoundingClientRect();
      setLengthDropdownPosition({
        top: rect.bottom + 10 + window.scrollY,
        left: rect.left + window.scrollX,
      });
    }
  }, [lengthDropDown]);

  const weightDropdownTriggerRef = useRef(null);
  const [weightDropdownPosition, setWeightDropdownPosition] = useState({
    top: 0,
    left: 0,
  });

  useEffect(() => {
    if (weightDropDown && weightDropdownTriggerRef.current) {
      const rect = weightDropdownTriggerRef.current.getBoundingClientRect();
      setWeightDropdownPosition({
        top: rect.bottom + 10 + window.scrollY,
        left: rect.left + window.scrollX,
      });
    }
  }, [weightDropDown]);

  return (
    <div>
      <h4 className="font-inter font-normal text-xl text-[#282828]">
        {__('Package Dimension', 'easycommerce')}
      </h4>
      <div className="flex flex-wrap gap-4 mt-4">
        <div>
          <h5 className="text-sm font-normal text-[#282828] font-inter mb-[6px]">
            {__('Height', 'easycommerce')}
          </h5>
          <div className="flex gap-2 grow-[1]">
            <div className="flex-1">
              <input
                type="number"
                name={`height-${priceItem.id}`}
                id={`height-${priceItem.id}`}
                className="h-ec-input p-4 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] shadow-none transition-colors duration-300 ease-in-out max-w-[153px]"
                placeholder={__('Set Height', 'easycommerce')}
                value={priceItem.meta?.height?.value || ''}
                onChange={(e) => {
                  setPriceItem((prev) => ({
                    ...prev,
                    meta: {
                      ...prev.meta,
                      height: {
                        value: e.target.value,
                        unit: heightUnit,
                      },
                    },
                  }));
                }}
                min={0}
              />
            </div>
            <div className="relative" ref={heightDropdownTriggerRef}>
              <button
                type="button"
                className="w-[80px] h-ec-input p-4 flex items-center justify-between font-inter text-base leading-6 text-ec-title border rounded-lg border-solid border-ec-table-stock hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out"
                onClick={() => setHeightDropDown(!heightDropDown)}
                onBlur={() => setHeightDropDown(false)}
              >
                <div className="font-normal font-sm leading-[20px] text-[#282828]">{heightUnit}</div>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="11"
                  height="6"
                  viewBox="0 0 11 6"
                  fill="none"
                >
                  <path
                    d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
                    fill="#3C3C42"
                  />
                </svg>
              </button>
              {heightDropDown &&
                createPortal(
                  <ul
                    className="absolute border bg-white border-ec-border rounded-lg shadow-2xl z-[99] min-w-[160px]"
                    style={{
                      top: heightDropdownPosition.top,
                      left: heightDropdownPosition.left,
                    }}
                  >
                    {units.height.map((unit, index) => (
                      <li
                        key={index}
                        className="px-4 py-3 text-[14px] text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] cursor-pointer rounded-[4px] m-0"
                        onMouseDown={() => {
                          setHeightUnit(unit.value);
                          setPriceItem((prev) => ({
                            ...prev,
                            meta: {
                              ...prev.meta,
                              height: {
                                value: prev.meta?.height?.value || '',
                                unit: unit.value,
                              },
                            },
                          }));
                        }}
                      >
                        {unit.label}
                      </li>
                    ))}
                  </ul>,
                  document.body
                )}
            </div>
          </div>
        </div>

        <div>
          <h5 className="text-sm font-normal text-[#282828] font-inter mb-[6px]">
            {__('Width', 'easycommerce')}
          </h5>
          <div className="flex gap-2 grow-[1]">
            <div className="flex-1">
              <input
                type="number"
                name={`width-${priceItem.id}`}
                id={`width-${priceItem.id}`}
                className="h-ec-input p-4 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] shadow-none transition-colors duration-300 ease-in-out max-w-[153px]"
                placeholder={__('Set Width', 'easycommerce')}
                value={priceItem.meta?.width?.value || ''}
                onChange={(e) => {
                  setPriceItem((prev) => ({
                    ...prev,
                    meta: {
                      ...prev.meta,
                      width: {
                        value: e.target.value,
                        unit: widthUnit,
                      },
                    },
                  }));
                }}
                min={0}
              />
            </div>
            <div className="relative" ref={widthDropdownTriggerRef}>
              <button
                type="button"
                className="w-[80px] h-ec-input p-4 flex items-center justify-between font-inter text-base leading-6 text-ec-title border rounded-lg border-solid border-ec-table-stock hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out"
                onClick={() => setWidthDropDown(!widthDropDown)}
                onBlur={() => setWidthDropDown(false)}
              >
                <div className="font-normal font-sm leading-[20px] text-[#282828]">{widthUnit}</div>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="11"
                  height="6"
                  viewBox="0 0 11 6"
                  fill="none"
                >
                  <path
                    d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
                    fill="#3C3C42"
                  />
                </svg>
              </button>
              {widthDropDown &&
                createPortal(
                  <ul
                    className="absolute border bg-white border-ec-border rounded-lg shadow-2xl z-[99] min-w-[160px]"
                    style={{
                      top: widthDropdownPosition.top,
                      left: widthDropdownPosition.left,
                    }}
                  >
                    {units.width.map((unit, index) => (
                      <li
                        key={index}
                        className="px-4 py-3 text-[14px] text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] cursor-pointer rounded-[4px] m-0"
                        onMouseDown={() => {
                          setWidthUnit(unit.value);
                          setPriceItem((prev) => ({
                            ...prev,
                            meta: {
                              ...prev.meta,
                              width: {
                                value: prev.meta?.width?.value || '',
                                unit: unit.value,
                              },
                            },
                          }));
                        }}
                      >
                        {unit.label}
                      </li>
                    ))}
                  </ul>,
                  document.body
                )}
            </div>
          </div>
        </div>

        <div>
          <h5 className="text-sm font-normal text-[#282828] font-inter mb-[6px]">
            {__('Length', 'easycommerce')}
          </h5>
          <div className="flex gap-2 grow-[1]">
            <div className="flex-1">
              <input
                type="number"
                name={`length-${priceItem.id}`}
                id={`length-${priceItem.id}`}
                className="h-ec-input p-4 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] shadow-none transition-colors duration-300 ease-in-out max-w-[153px]"
                placeholder={__('Set Length', 'easycommerce')}
                value={priceItem.meta?.length?.value || ''}
                onChange={(e) => {
                  setPriceItem((prev) => ({
                    ...prev,
                    meta: {
                      ...prev.meta,
                      length: {
                        value: e.target.value,
                        unit: lengthUnit,
                      },
                    },
                  }));
                }}
                min={0}
              />
            </div>
            <div className="relative" ref={lengthDropdownTriggerRef}>
              <button
                type="button"
                className="w-[80px] h-ec-input p-4 flex items-center justify-between font-inter text-base leading-6 text-ec-title border rounded-lg border-solid border-ec-table-stock hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out"
                onClick={() => setLengthDropDown(!lengthDropDown)}
                onBlur={() => setLengthDropDown(false)}
              >
                <div className="font-normal font-sm leading-[20px] text-[#282828]">{lengthUnit}</div>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="11"
                  height="6"
                  viewBox="0 0 11 6"
                  fill="none"
                >
                  <path
                    d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
                    fill="#3C3C42"
                  />
                </svg>
              </button>
              {lengthDropDown &&
                createPortal(
                  <ul
                    className="absolute border bg-white border-ec-border rounded-lg shadow-2xl z-[99] min-w-[160px]"
                    style={{
                      top: lengthDropdownPosition.top,
                      left: lengthDropdownPosition.left,
                    }}
                  >
                    {units.length.map((unit, index) => (
                      <li
                        key={index}
                        className="px-4 py-3 text-[14px] text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] cursor-pointer rounded-[4px] m-0"
                        onMouseDown={() => {
                          setLengthUnit(unit.value);
                          setPriceItem((prev) => ({
                            ...prev,
                            meta: {
                              ...prev.meta,
                              length: {
                                value: prev.meta?.length?.value || '',
                                unit: unit.value,
                              },
                            },
                          }));
                        }}
                      >
                        {unit.label}
                      </li>
                    ))}
                  </ul>,
                  document.body
                )}
            </div>
          </div>
        </div>

        <div>
          <h5 className="text-sm font-normal text-[#282828] font-inter mb-[6px]">
            {__('Weight', 'easycommerce')}
          </h5>
          <div className="flex gap-2 grow-[1]">
            <div className="flex-1">
              <input
                type="number"
                name={`weight-${priceItem.id}`}
                id={`weight-${priceItem.id}`}
                className="h-ec-input p-4 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] shadow-none transition-colors duration-300 ease-in-out max-w-[153px]"
                placeholder={__('Set Weight', 'easycommerce')}
                value={priceItem.meta?.weight?.value || ''}
                onChange={(e) => {
                  setPriceItem((prev) => ({
                    ...prev,
                    meta: {
                      ...prev.meta,
                      weight: {
                        value: e.target.value,
                        unit: weightUnit,
                      },
                    },
                  }));
                }}
                min={0}
              />
            </div>
            <div className="relative" ref={weightDropdownTriggerRef}>
              <button
                type="button"
                className="w-[80px] h-ec-input p-4 flex items-center justify-between font-inter text-base leading-6 text-ec-title border rounded-lg border-solid border-ec-table-stock hover:border-ec-primary focus:border-ec-primary focus:outline-none focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out"
                onClick={() => setWeightDropDown(!weightDropDown)}
                onBlur={() => setWeightDropDown(false)}
              >
                <div className="font-normal font-sm leading-[20px] text-[#282828]">{weightUnit}</div>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="11"
                  height="6"
                  viewBox="0 0 11 6"
                  fill="none"
                >
                  <path
                    d="M9.87109 1.71094L5.71484 5.62109C5.56901 5.7487 5.41406 5.8125 5.25 5.8125C5.08594 5.8125 4.9401 5.7487 4.8125 5.62109L0.65625 1.71094C0.382812 1.40104 0.373698 1.09115 0.628906 0.78125C0.920573 0.507812 1.23047 0.498698 1.55859 0.753906L5.25 4.25391L8.96875 0.753906C9.27865 0.498698 9.57943 0.498698 9.87109 0.753906C10.1263 1.08203 10.1263 1.40104 9.87109 1.71094Z"
                    fill="#3C3C42"
                  />
                </svg>
              </button>
              {weightDropDown &&
                createPortal(
                  <ul
                    className="absolute border bg-white border-ec-border rounded-lg shadow-2xl z-[99] min-w-[160px]"
                    style={{
                      top: weightDropdownPosition.top,
                      left: weightDropdownPosition.left,
                    }}
                  >
                    {units.weight.map((unit, index) => (
                      <li
                        key={index}
                        className="px-4 py-3 text-[14px] text-ec-body font-normal leading-[26px] hover:bg-[#F8F8F8] cursor-pointer rounded-[4px] m-0"
                        onMouseDown={() => {
                          setWeightUnit(unit.value);
                          setPriceItem((prev) => ({
                            ...prev,
                            meta: {
                              ...prev.meta,
                              weight: {
                                value: prev.meta?.weight?.value || '',
                                unit: unit.value,
                              },
                            },
                          }));
                        }}
                      >
                        {unit.label}
                      </li>
                    ))}
                  </ul>,
                  document.body
                )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dimensions;
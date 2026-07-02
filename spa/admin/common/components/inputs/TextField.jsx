import React from 'react'

const TextField = ({label, className = '', ...rest}) => {
    const baseCLass = `p-4 h-ec-input rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none 
    focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full disabled:cursor-not-allowed disabled:bg-ec-table-stock disabled:hover:border-ec-table-stock disabled:focus:border-ec-table-stock px-3 py-2 `

    return (
        <>
            {label && <label className="text-[#0A0A0A] font-normal text-sm block mb-2">{label}</label>}
            <input className={baseCLass + className} type="text" {...rest} />
        </>
    )
}

export default TextField;
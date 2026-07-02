import React from 'react'

const TextArea = ({className = '', ...rest}) => {
    const baseCLass = `p-4 rounded-lg font-inter text-[14px] leading-[20px] border border-ec-table-stock placeholder-ec-placeholder hover:border-ec-primary focus:border-ec-primary focus:outline-none 
    focus:[box-shadow:0_0_0_4px_#F3F0FF] transition-colors duration-300 ease-in-out w-full h-[102px] `

    return (
        <textarea className={baseCLass + className} type="text" {...rest} />
    )
}

export default TextArea
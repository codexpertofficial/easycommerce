import React from 'react'

const Button = ({className, value, ...rest}) => {
    return (
        <button className={className} {...rest}>{value}</button>
    )
}

export default Button
import React from "react";

const TFoot = ({ total }) => {
    return (
        <tfoot>
            <tr className="grid grid-cols-12 gap-0 p-0">
                <td className="col-span-8 p-0 pt-5 pl-4 text-left border-0">
                    <span className="font-inter font-medium text-base leading-[26px] text-ec-title">
                        Total
                    </span>
                </td>
                <td className="col-span-2 p-0 pt-5 border-0"></td>
                <td className="col-span-2 p-0 pt-5 pr-4 text-right border-0">
                    <span className="font-inter font-medium text-base leading-[26px] text-ec-title">
                        {total}
                    </span>
                </td>
            </tr>
        </tfoot>
    );
};

export default TFoot;

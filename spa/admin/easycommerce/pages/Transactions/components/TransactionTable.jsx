import React from "react";
import TableBody from "./TableBody";

const TransactionTable = ({ transactions, tableColumns}) => {
    return (
        <div className="w-full overflow-y-hidden xl:overflow-x-auto">
			<table className="min-w-full xl:min-w-[1300px] w-full border-collapse border-spacing-0"> 
                <TableBody
                    transactions={transactions}
                    tableColumns={tableColumns}
                />
            </table>
        </div>
    );
};

export default TransactionTable;

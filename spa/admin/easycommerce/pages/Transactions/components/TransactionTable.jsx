import React from "react";
import TableBody from "./TableBody";

const TransactionTable = ({ transactions, tableColumns}) => {
    return (
        <div className="w-full">
			<table className="w-full border-collapse border-spacing-0"> 
                <TableBody
                    transactions={transactions}
                    tableColumns={tableColumns}
                />
            </table>
        </div>
    );
};

export default TransactionTable;

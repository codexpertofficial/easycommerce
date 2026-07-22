import { twMerge } from "tailwind-merge";
import { motion } from "framer-motion";
import { __ } from "@wordpress/i18n";

const ToggleSwitch = ({ checked, handleChecked, id = 'checkbox' }) => {
    return (
        <div className="flex space-x-4 antialiased items-center">
            <label
                htmlFor={id}
                className={twMerge(
                    `flex items-center border border-transparent rounded-full relative cursor-pointer transition duration-200`,
                    "w-[95px] h-[36px] p-[5px]", // Updated width to 95px
                    checked ? "bg-[#28A645]" : "bg-ec-light-black"
                )}
            >
                <motion.span
                    initial={{ opacity: 0, x: -10 }}
                    animate={{
                        opacity: checked ? 1 : 0,
                        x: checked ? 5 : -10,
                    }}
                    transition={{ duration: 0.3 }}
                    className={twMerge(
                        "absolute left-3 text-white font-bold font-inter text-xs" // Adjusted font size and spacing
                    )}
                >
                    { __( "Active", "easycommerce" ) }
                </motion.span>

                <motion.span
                    initial={{ opacity: 0, x: 10 }}
                    animate={{
                        opacity: checked ? 0 : 1,
                        x: checked ? -15 : 0,
                    }}
                    transition={{ duration: 0.3 }}
                    className={twMerge(
                        "absolute right-3 text-white font-medium font-inter text-xs" // Adjusted font size and spacing
                    )}
                >
                    { __( "Inactive", "easycommerce" ) }
                </motion.span>

                <motion.div
                    initial={{
                        width: "26px",
                        x: checked ? 0 : 57, // Adjusted to fit the new width of the switch
                    }}
                    animate={{
                        height: ["26px", "15px", "26px"],
                        width: ["26px", "36px", "26px", "26px"], // Updated circle size
                        x: checked ? 57 : 0, // Adjusted x position based on checked status
                    }}
                    transition={{
                        duration: 0.3,
                        delay: 0.1,
                    }}
                    key={String(checked)}
                    className={twMerge(
                        "h-[26px] w-[26px] block rounded-full bg-white shadow-md z-10" // Updated circle dimensions
                    )}
                ></motion.div>

                <input
                    type="checkbox"
                    checked={checked}
                    onChange={handleChecked}
                    className="!hidden"
                    id={id}
                />
            </label>
        </div>
    );
};

export default ToggleSwitch;

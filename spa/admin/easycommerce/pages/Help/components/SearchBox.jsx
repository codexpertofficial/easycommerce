import { __ } from "@wordpress/i18n";

export default function SearchBox({ assetsPath }) {
    //Icons
    const searchIcon = `${EASYCOMMERCE.assets}admin/img/icons/search.png`;

    return (
        <>
            <div className="w-[632px] bg-white mx-auto rounded-xl shadow-search-box-shadow h-14 absolute left-1/2 -translate-x-1/2 -bottom-7">
                <form className="w-full flex relative">
                    <img
                        src={searchIcon}
                        className="w-4 h-4 absolute z-10 top-1/2 -translate-y-1/2 left-[15px]"
                        alt={__("Search Icon", "easycommerce")}
                    />
                    <input
                        type="text"
                        className="font-inter easycommerce-help-search-input p-r-[100px] w-full border-0 h-14 pr-8 pl-5 rounded-xl z-0 focus:outline-none"
                        placeholder={__("Ask a question", "easycommerce")}
                    />
                    <button
                        type="submit"
                        className="font-inter absolute right-[7px] top-[7px] rounded-lg text-white text-base leading-[26px] py-2 px-4 inline-block bg-ec-primary"
                    >
                        {__("Search", "easycommerce")}
                    </button>
                </form>
            </div>
        </>
    );
}

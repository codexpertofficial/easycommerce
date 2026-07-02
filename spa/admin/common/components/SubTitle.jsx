import Tooltip from './Tooltip';

const SubTitle = ({ SubTitle, notice = ''}) => {
    return (
        <>
            <div class="flex gap-3 items-center">
                <h3 className="font-inter text-base leading-[32px] font-medium text-[#121216]">
                    {SubTitle}
                </h3>
                {notice && (
                    <Tooltip text={notice} />
                )}
            </div>
        </>
    )
}

export default SubTitle;
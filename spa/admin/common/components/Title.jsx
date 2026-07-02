import Tooltip from './Tooltip';

const Title = ({ title, notice = ''}) => {
    return (
        <>
            <div class="flex gap-3 items-center">
                <h3 className="font-inter text-lg leading-[32px] font-medium text-[#121216]">
                    {title}
                </h3>
                {notice && (
                    <Tooltip text={notice} />
                )}
            </div>
        </>
    )
}

export default Title;
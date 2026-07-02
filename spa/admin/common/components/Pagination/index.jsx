// Elements
import PageNumbers from "./elements/PageNumbers";
import Previous from "./elements/Previous";
import Next from "./elements/Next";

const Pagination = ({ baseSlug = "products", current = 1, total = 1 }) => {
    return (
        <div className="flex justify-center items-center gap-[6px] my-10">
            <Previous baseSlug={baseSlug} current={current} />

            <PageNumbers total={total} current={current} baseSlug={baseSlug} />

            <Next baseSlug={baseSlug} current={current} total={total} />
        </div>
    );
};

export default Pagination;

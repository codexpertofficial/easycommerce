import React, { useRef } from "react";
import { useDrag, useDrop } from 'react-dnd';

const ItemType = 'ITEM';

const Item = ({ item, index, moveItem, fromList, children }) => {
    const ref = useRef(null);
    const dragHandleRef = useRef(null);

    const [{ isDragging }, drag, preview] = useDrag({
        type: ItemType,
        item: { id: item.id, index, fromList },
        collect: (monitor) => ({
            isDragging: monitor.isDragging(),
        }),
    });

    const [, drop] = useDrop({
        accept: ItemType,
        hover(dragged, monitor) {
            if (!ref.current) return;
            if (dragged.fromList === fromList && dragged.index === index) return;

            const hoverBoundingRect = ref.current.getBoundingClientRect();
            const hoverMiddleY = (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;
            const clientOffset = monitor.getClientOffset();
            const hoverClientY = clientOffset.y - hoverBoundingRect.top;

            const shouldMoveDown = dragged.index < index && hoverClientY < hoverMiddleY;
            const shouldMoveUp = dragged.index > index && hoverClientY > hoverMiddleY;
            if (shouldMoveDown || shouldMoveUp) return;

            moveItem(dragged.fromList, fromList, dragged.index, index);
            dragged.index = index;
            dragged.fromList = fromList;
        },
    });

    drag(dragHandleRef);
    preview(ref);
    drop(ref);

    return (
        <div
            ref={ref}
            className={`
                transition-all duration-200 ease-in-out rounded-md border px-4 py-2 mb-2 bg-white shadow-sm
                flex items-center justify-between
                ${isDragging ? 'opacity-50 border-[2px] border-dashed border-ec-primary' : 'cursor-default'}
            `}
        >
            {children}

            <button
                ref={dragHandleRef}
                className="p-1 bg-gray-200 rounded hover:bg-gray-300 cursor-move"
                aria-label="Drag handle"
                type="button"
            >
                ☰
            </button>
        </div>
    );
};

const DroppableList = ({ items, listKey, moveItem, children }) => {
    const [{ canDrop }, drop] = useDrop({
        accept: ItemType,
        drop: (dragged, monitor) => {
            const didDropOnItem = monitor.didDrop();
            if (!didDropOnItem) {
                const insertIndex = items.length;
                moveItem(dragged.fromList, listKey, dragged.index, insertIndex);
                dragged.index = insertIndex;
                dragged.fromList = listKey;
            }
        },
        collect: (monitor) => ({
            canDrop: monitor.canDrop(),
        }),
    });

    return (
        <div
            ref={drop}
            className={`min-h-[100px] p-4 rounded-lg transition-colors duration-200
                ${canDrop ? 'border-[2px] border-dashed border-ec-primary' : 'border-[2px] border-dashed border-transparent'}`}
        >
            {children}
        </div>
    );
};

export { Item, DroppableList };
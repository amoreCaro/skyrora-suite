import { useState } from "react";

export const Repeater = () => {
  const [items, setItems] = useState([{ title: "", text: "" }]);
  const [openedItems, setOpenedItems] = useState([]);

  const handleChange = (index, field, value) => {
    const newItems = [...items];
    newItems[index][field] = value;
    setItems(newItems);
  };

  const toggleItem = (index) => {
    setOpenedItems((prevState) => {
      const newState = [...prevState];
      newState[index] = !newState[index];
      return newState;
    });
  };

  const addItem = () => {
    setItems([...items, { title: "", text: "" }]);
    setOpenedItems([...openedItems, false]); 
  };

  const duplicateItem = (index) => {
    const newItems = [...items];
    const newOpenedItems = [...openedItems];

    const itemToDuplicate = { ...newItems[index] }; 
    newItems.splice(index + 1, 0, itemToDuplicate); 
    newOpenedItems.splice(index + 1, 0, false);

    setItems(newItems);
    setOpenedItems(newOpenedItems);
  };

  const deleteItem = (index) => {
    const newItems = items.filter((_, i) => i !== index); 
    const newOpenedItems = openedItems.filter((_, i) => i !== index);
    setItems(newItems);
    setOpenedItems(newOpenedItems);
  };

  return (
    <div className="repeater__wrapper">
      <h3 className="repeater__title">Items</h3>

      <div className="repeater__list">
        {items.map((item, index) => (
          <div
            key={index}
            className={`repeater__item ${openedItems[index] ? "opened" : ""}`}
          >
            <div className="repeater__item-preview">
              <div
                className="repeater__item-title"
                onClick={() => toggleItem(index)}
              >
                Item {index + 1}
              </div>

              <div className="repeater__item-btns">
                <button
                  type="button"
                  className="repeater__item-duplicate-btn"
                  onClick={() => duplicateItem(index)}
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    fill="#000000"
                    viewBox="0 0 256 256"
                  >
                    <path d="M216,32H88a8,8,0,0,0-8,8V80H40a8,8,0,0,0-8,8V216a8,8,0,0,0,8,8H168a8,8,0,0,0,8-8V176h40a8,8,0,0,0,8-8V40A8,8,0,0,0,216,32ZM160,208H48V96H160Zm48-48H176V88a8,8,0,0,0-8-8H96V48H208Z"></path>
                  </svg>
                </button>

                <button
                  type="button"
                  className="repeater__item-delete-btn"
                  onClick={() => deleteItem(index)}
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    fill="#000000"
                    viewBox="0 0 256 256"
                  >
                    <path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"></path>
                  </svg>
                </button>
              </div>
            </div>

            {openedItems[index] && (
              <div className="repeater__item-editor-wrapper">
                <div className="repeater__item-editor">
                  <div className="form-text">
                    <input
                      type="text"
                      placeholder="Title"
                      value={item.title}
                      onChange={(e) =>
                        handleChange(index, "title", e.target.value)
                      }
                      className="components-text-control__input"
                    />
                  </div>

                  <div className="form-textarea">
                    <textarea
                      placeholder="Text"
                      value={item.text}
                      onChange={(e) =>
                        handleChange(index, "text", e.target.value)
                      }
                      className="components-text-control__input"
                    ></textarea>
                  </div>
                </div>
              </div>
            )}
          </div>
        ))}
      </div>

      <button type="button" className="repeater__add-item-btn" onClick={addItem}>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          fill="#000000"
          viewBox="0 0 256 256"
        >
          <path d="M224,128a8,8,0,0,1-8,8H136v80a8,8,0,0,1-16,0V136H40a8,8,0,0,1,0-16h80V40a8,8,0,0,1,16,0v80h80A8,8,0,0,1,224,128Z"></path>
        </svg>
        Add item
      </button>
    </div>
  );
};

import React from "react";
<script src="https://kit.fontawesome.com/23df55ab57.js" crossorigin="anonymous"></script>
const BtArrow = ({ dispatch, alt, type }) => {
    return (
        <img src="/img.png" className={["arrow, type"].join(" ")}
        alt={alt}
        onClick={() =>
            dispatch({
                type: type === "top" ? "DECREMENT" : "INCREMENT"})
        }
        />
    );
};

export default BtArrow;
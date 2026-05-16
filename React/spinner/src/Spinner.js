import React from "react";
import { useReducer } from "react";
import BtArrow from "./BtArrow";
// Reducer des actions

const reducer = (state, action) => {
  switch (action.type) {
    case "INCREMENT":
      return { count: state.count + 1 };
    case "DECREMENT":
      return { count: state.count - 1 };
    default:
      return state;
  }
};

const Spinner = ({ start }) => {
  const [state, dispatch] = useReducer(reducer, { count: start });
  return (
    <div>
      <BtArrow type="top" alt="left arrow" dispatch={dispatch} />
      <div className="box">
        <div className="numbers1">{state.count}</div>
      </div>
      <BtArrow type="bottom" alt="right arrow" dispatch={dispatch} />
    </div>
  );
};

export default Spinner;
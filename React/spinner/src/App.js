import React from 'react';
import './App.css';
import Spinner from './Spinner';

function App() {
  return (
    <div className="App">
      <Spinner start={0} />
      <Spinner start={5} />
      <Spinner start={10} />
    </div>
  );
}

export default App;

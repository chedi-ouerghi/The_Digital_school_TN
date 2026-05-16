// App.js
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Login from './components/login';
import Dashboard from './components/Dashboard';
import Form from './components/Form';
import Checklist from './components/Checklists';
import UpdateForm from './components/UpdateForm';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/" element={<Login />} />
        <Route path="/form" element={<Form />} />
        <Route path="/checklist/:id" element={<Checklist />} />
        <Route path="/checklist/update/:id" element={<UpdateForm />} />
      </Routes>
    </Router>
  );
}

export default App;

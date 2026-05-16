import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import instance from '../api/axiosConfig';
import './signup-login.css';

const Form = () => {
    const [formData, setFormData] = useState({
        id: 0,
        title: '',
        description: '',
        statut: 0,
        todo: [{ title: '', description: '', state: 0 }]
    });

    const navigate = useNavigate();

    const handleChange = (e) => {
        const { name, value } = e.target;
        // Convert statut to number
        const finalValue = name === 'statut' ? parseInt(value) || 0 : value;
        setFormData((prevData) => ({ ...prevData, [name]: finalValue }));
    };

    const handleTodoChange = (index, e) => {
        const { name, value } = e.target;
        const todo = [...formData.todo];
        // Convert state to number if it's the state field
        todo[index][name] = name === 'state' ? parseInt(value) || 0 : value;
        setFormData((prevData) => ({ ...prevData, todo }));
    };

    const handleAddTodo = () => {
        setFormData((prevData) => ({
            ...prevData,
            todo: [...prevData.todo, { title: '', description: '', state: 0 }]
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            // Validate that title and description are not empty
            if (!formData.title.trim() || !formData.description.trim()) {
                alert('Title and description are required');
                return;
            }
            
            // Validate that at least one todo has title and description
            if (formData.todo.some(todo => !todo.title.trim() || !todo.description.trim())) {
                alert('All tasks must have title and description');
                return;
            }

            // Create payload without ID for new checklist creation
            const payload = {
                title: formData.title,
                description: formData.description,
                statut: formData.statut,
                todo: formData.todo
            };

            const response = await instance.post('/checklist/add', payload);
            const data = response.data;
            console.log('Checklist added:', data);
            navigate('/dashboard');
        } catch (error) {
            console.error('Error adding checklist:', error);
            const errorMsg = error.response?.data?.error || error.message || 'Unknown error';
            alert('Error adding checklist: ' + errorMsg);
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 p-8">
            <div className="container mx-auto max-w-3xl">
                {/* Header */}
                <div className="mb-8">
                    <Link to="/dashboard" className="text-indigo-600 hover:text-indigo-800 font-semibold mb-4 inline-block">
                        ← Back to Dashboard
                    </Link>
                    <h1 className="text-4xl font-bold text-gray-900 mb-2">Create New Checklist</h1>
                    <p className="text-gray-600">Organize your tasks and stay on track</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-8">
                    {/* Checklist Info */}
                    <div className="card">
                        <h2 className="text-2xl font-bold mb-6 text-gray-900">Checklist Details</h2>
                        
                        <div className="form-group">
                            <label htmlFor="title">Checklist Title</label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                placeholder="e.g., Project Planning, Daily Standup"
                                value={formData.title}
                                onChange={handleChange}
                                required
                            />
                        </div>

                        <div className="form-group">
                            <label htmlFor="description">Description</label>
                            <textarea
                                id="description"
                                name="description"
                                placeholder="Describe your checklist..."
                                value={formData.description}
                                onChange={handleChange}
                                required
                            />
                        </div>

                        <div className="form-group">
                            <label htmlFor="statut">Status</label>
                            <select
                                id="statut"
                                name="statut"
                                value={formData.statut}
                                onChange={handleChange}
                            >
                                <option value="0">📋 Pending</option>
                                <option value="1">⚙️ In Progress</option>
                                <option value="2">✅ Completed</option>
                            </select>
                        </div>
                    </div>

                    {/* Tasks Section */}
                    <div className="card">
                        <div className="flex justify-between items-center mb-6">
                            <h2 className="text-2xl font-bold text-gray-900">Tasks</h2>
                            <span className="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-semibold">
                                {formData.todo.length}
                            </span>
                        </div>

                        {formData.todo.map((todoItem, index) => (
                            <div key={index} className="mb-6 p-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border-2 border-gray-200">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="font-semibold text-gray-900">Task {index + 1}</h3>
                                    {index > 0 && (
                                        <button
                                            type="button"
                                            onClick={() => {
                                                const newTodo = formData.todo.filter((_, i) => i !== index);
                                                setFormData(prev => ({ ...prev, todo: newTodo }));
                                            }}
                                            className="text-red-600 hover:text-red-800 font-semibold text-sm"
                                        >
                                            Remove
                                        </button>
                                    )}
                                </div>

                                <div className="form-group">
                                    <label htmlFor={`todoTitle${index}`}>Task Title</label>
                                    <input
                                        type="text"
                                        id={`todoTitle${index}`}
                                        name="title"
                                        placeholder="What needs to be done?"
                                        value={todoItem.title}
                                        onChange={(e) => handleTodoChange(index, e)}
                                        required
                                    />
                                </div>

                                <div className="form-group">
                                    <label htmlFor={`todoDescription${index}`}>Task Description</label>
                                    <textarea
                                        id={`todoDescription${index}`}
                                        name="description"
                                        placeholder="Add details about this task..."
                                        value={todoItem.description}
                                        onChange={(e) => handleTodoChange(index, e)}
                                        required
                                    />
                                </div>

                                <div className="form-group">
                                    <label htmlFor={`todoState${index}`}>Task State</label>
                                    <select
                                        id={`todoState${index}`}
                                        name="state"
                                        value={todoItem.state}
                                        onChange={(e) => handleTodoChange(index, e)}
                                    >
                                        <option value="0">🔲 Not Started</option>
                                        <option value="1">⏳ In Progress</option>
                                        <option value="2">✔️ Completed</option>
                                    </select>
                                </div>
                            </div>
                        ))}

                        <button
                            type="button"
                            onClick={handleAddTodo}
                            className="w-full btn-secondary"
                        >
                            + Add Another Task
                        </button>
                    </div>

                    {/* Actions */}
                    <div className="flex gap-4">
                        <Link to="/dashboard" className="flex-1 text-center py-3 px-6 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            className="flex-1 btn-success"
                        >
                            Save Checklist
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default Form;


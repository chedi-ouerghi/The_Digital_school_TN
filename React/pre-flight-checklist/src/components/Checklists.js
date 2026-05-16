import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import instance from '../api/axiosConfig';
import { getAuthToken } from './Auth';
import './signup-login.css';

const Checklist = () => {
    const { id } = useParams();
    const [checklist, setChecklist] = useState(null);
    const token = getAuthToken();

    useEffect(() => {
        const fetchChecklist = async () => {
            try {
                const response = await instance.get(`/checklist/${id}`);
                setChecklist(response.data.response);
            } catch (error) {
                console.error('Error fetching checklist:', error);
            }
        };

        if (token) {
            fetchChecklist();
        }
    }, [id, token]);

    if (!checklist) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center p-8">
                <div className="card text-center">
                    <div className="text-6xl mb-4">⏳</div>
                    <p className="text-xl text-gray-600">Loading checklist...</p>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 p-8">
            <div className="container mx-auto max-w-3xl">
                {/* Header */}
                <div className="mb-8">
                    <Link to="/dashboard" className="text-indigo-600 hover:text-indigo-800 font-semibold mb-4 inline-block">
                        ← Back to Dashboard
                    </Link>
                    <h1 className="text-4xl font-bold text-gray-900 mb-2">{checklist.title}</h1>
                    <p className="text-gray-600 text-lg">{checklist.description}</p>
                </div>

                {/* Status */}
                <div className="card mb-8">
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-xl font-bold">Details</h2>
                        <Link to={`/checklist/update/${id}`} className="btn-success">
                            Edit Checklist
                        </Link>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <p className="text-gray-600 text-sm mb-2">Status</p>
                            <span className={`badge ${
                                checklist.statut === 0 ? 'badge-pending' :
                                checklist.statut === 1 ? 'badge-progress' :
                                'badge-completed'
                            }`}>
                                {checklist.statut === 0 ? '📋 Pending' :
                                 checklist.statut === 1 ? '⚙️ In Progress' :
                                 '✅ Completed'}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Tasks */}
                <div className="card">
                    <h2 className="text-2xl font-bold mb-6">Tasks ({checklist.todo?.length || 0})</h2>
                    <div className="space-y-4">
                        {checklist.todo && checklist.todo.map((todoItem, index) => (
                            <div key={index} className="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border-l-4 border-indigo-500 hover:shadow-md transition">
                                <div className="flex justify-between items-start mb-2">
                                    <h3 className="text-lg font-semibold text-gray-900">{todoItem.title}</h3>
                                    <span className={`badge ${
                                        todoItem.state === 0 ? 'badge-pending' :
                                        todoItem.state === 1 ? 'badge-progress' :
                                        'badge-completed'
                                    }`}>
                                        {todoItem.state === 0 ? '🔲 Not Started' :
                                         todoItem.state === 1 ? '⏳ In Progress' :
                                         '✔️ Completed'}
                                    </span>
                                </div>
                                <p className="text-gray-600">{todoItem.description}</p>
                            </div>
                        ))}
                        {(!checklist.todo || checklist.todo.length === 0) && (
                            <div className="text-center py-8 text-gray-500">
                                <p className="text-lg">No tasks added yet</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Checklist;

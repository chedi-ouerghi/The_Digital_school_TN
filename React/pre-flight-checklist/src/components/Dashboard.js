import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import instance from '../api/axiosConfig';
import { getAuthToken } from './Auth';
import './signup-login.css';

const Dashboard = () => {
    const [checklists, setChecklists] = useState([]);
    const token = getAuthToken();

    useEffect(() => {
        const fetchChecklists = async () => {
            if (!token) {
                console.error('No token found');
                return;
            }

            try {
                const response = await instance.get('/checklists');
                setChecklists(response.data.response);
            } catch (error) {
                console.error('Error fetching checklists:', error);
            }
        };

        fetchChecklists();
    }, [token]);

    const handleDelete = async (id) => {
        if (!token) {
            console.error('No token found');
            return;
        }

        try {
            const response = await instance.delete(`/checklist/delete/${id}`);

            if (response.data.success) {
                setChecklists((prevChecklists) => prevChecklists.filter((checklist) => checklist.id !== id));
            } else {
                alert('Error deleting checklist');
            }
        } catch (error) {
            console.error('Error deleting checklist:', error);
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 p-8">
            <div className="container mx-auto">
                {/* Header */}
                <div className="flex justify-between items-center mb-12">
                    <div>
                        <h1 className="text-4xl font-bold text-gray-900 mb-2">My Checklists</h1>
                        <p className="text-gray-600">Manage and track your tasks efficiently</p>
                    </div>
                    <Link to="/form" className="btn-primary">
                        + New Checklist
                    </Link>
                </div>

                {/* Checklists Grid or Table */}
                {checklists.length > 0 ? (
                    <div>
                        {/* Desktop Table View */}
                        <div className="hidden md:block">
                            <table className="w-full">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {checklists.map((checklist) => (
                                        <tr key={checklist.id}>
                                            <td className="font-semibold">{checklist.title}</td>
                                            <td className="text-gray-600 max-w-xs truncate">{checklist.description}</td>
                                            <td>
                                                <span className={`badge ${
                                                    checklist.statut === 0 ? 'badge-pending' :
                                                    checklist.statut === 1 ? 'badge-progress' :
                                                    'badge-completed'
                                                }`}>
                                                    {checklist.statut === 0 ? 'Pending' : 
                                                     checklist.statut === 1 ? 'In Progress' : 
                                                     'Completed'}
                                                </span>
                                            </td>
                                            <td>
                                                <div className="flex gap-2">
                                                    <Link to={`/checklist/${checklist.id}`} className="text-blue-600 hover:text-blue-800 font-semibold">
                                                        View
                                                    </Link>
                                                    <Link to={`/checklist/update/${checklist.id}`} className="text-green-600 hover:text-green-800 font-semibold">
                                                        Edit
                                                    </Link>
                                                    <button 
                                                        onClick={() => {
                                                            if(window.confirm('Are you sure?')) {
                                                                handleDelete(checklist.id);
                                                            }
                                                        }} 
                                                        className="text-red-600 hover:text-red-800 font-semibold"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Mobile Card View */}
                        <div className="md:hidden grid grid-cols-1 gap-4">
                            {checklists.map((checklist) => (
                                <div key={checklist.id} className="card">
                                    <h3 className="font-bold text-lg mb-2">{checklist.title}</h3>
                                    <p className="text-gray-600 text-sm mb-3">{checklist.description}</p>
                                    <div className="mb-4">
                                        <span className={`badge ${
                                            checklist.statut === 0 ? 'badge-pending' :
                                            checklist.statut === 1 ? 'badge-progress' :
                                            'badge-completed'
                                        }`}>
                                            {checklist.statut === 0 ? 'Pending' : 
                                             checklist.statut === 1 ? 'In Progress' : 
                                             'Completed'}
                                        </span>
                                    </div>
                                    <div className="flex gap-2">
                                        <Link to={`/checklist/${checklist.id}`} className="flex-1 text-center btn-secondary text-sm">
                                            View
                                        </Link>
                                        <Link to={`/checklist/update/${checklist.id}`} className="flex-1 text-center btn-success text-sm">
                                            Edit
                                        </Link>
                                        <button 
                                            onClick={() => {
                                                if(window.confirm('Are you sure?')) {
                                                    handleDelete(checklist.id);
                                                }
                                            }} 
                                            className="flex-1 btn-danger text-sm"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                ) : (
                    <div className="card text-center py-16">
                        <div className="text-6xl mb-4">📋</div>
                        <h2 className="text-2xl font-bold text-gray-900 mb-2">No Checklists Yet</h2>
                        <p className="text-gray-600 mb-6">Create your first checklist to get started</p>
                        <Link to="/form" className="btn-primary">
                            Create First Checklist
                        </Link>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Dashboard;



import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';

export default function FaqAdmin() {
    const [faqs, setFaqs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [editingId, setEditingId] = useState(null);
    const [newFaq, setNewFaq] = useState({ question: '', answer: '' });
    const [showAddForm, setShowAddForm] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [notification, setNotification] = useState(null);

    // Charger les FAQs
    const loadFaqs = async () => {
        try {
            const response = await fetch('/api/faq');
            const data = await response.json();
            // L'API retourne {faqs: {data: [...]}}, on extrait le tableau
            const faqsArray = data.faqs?.data || data.data || data;
            setFaqs(Array.isArray(faqsArray) ? faqsArray : []);
        } catch (error) {
            console.error('Erreur lors du chargement des FAQs:', error);
            showNotification('Erreur lors du chargement des FAQs', 'error');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadFaqs();
    }, []);

    // Afficher une notification
    const showNotification = (message, type = 'success') => {
        setNotification({ message, type });
        setTimeout(() => setNotification(null), 3000);
    };

    // Ajouter une nouvelle FAQ
    const handleAddFaq = async (e) => {
        e.preventDefault();
        if (!newFaq.question.trim() || !newFaq.answer.trim()) {
            showNotification('Veuillez remplir tous les champs', 'error');
            return;
        }

        try {
            const response = await fetch('/api/faq', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(newFaq)
            });

            if (response.ok) {
                const createdFaq = await response.json();
                setFaqs([...faqs, createdFaq]);
                setNewFaq({ question: '', answer: '' });
                setShowAddForm(false);
                showNotification('FAQ ajoutée avec succès');
            } else {
                throw new Error('Erreur lors de l\'ajout');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showNotification('Erreur lors de l\'ajout de la FAQ', 'error');
        }
    };

    // Mettre à jour une FAQ
    const handleUpdateFaq = async (id, updatedData) => {
        try {
            const response = await fetch(`/api/faq/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(updatedData)
            });

            if (response.ok) {
                const updatedFaq = await response.json();
                setFaqs(faqs.map(faq => faq.id === id ? updatedFaq : faq));
                setEditingId(null);
                showNotification('FAQ mise à jour avec succès');
            } else {
                throw new Error('Erreur lors de la mise à jour');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showNotification('Erreur lors de la mise à jour', 'error');
        }
    };

    // Supprimer une FAQ
    const handleDeleteFaq = async (id) => {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette FAQ ?')) return;

        try {
            const response = await fetch(`/api/faq/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                setFaqs(faqs.filter(faq => faq.id !== id));
                showNotification('FAQ supprimée avec succès');
            } else {
                throw new Error('Erreur lors de la suppression');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showNotification('Erreur lors de la suppression', 'error');
        }
    };

    // Filtrer les FAQs selon le terme de recherche
    const filteredFaqs = faqs.filter(faq =>
        faq.question.toLowerCase().includes(searchTerm.toLowerCase()) ||
        faq.answer.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // Debug: affichage d'informations de débogage
    console.log('FaqAdmin render:', { faqs, loading, faqsLength: faqs.length });

    if (loading) {
        return (
            <>
                <Head title="Administration FAQ" />
                <div className="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 flex items-center justify-center">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
                        <p className="mt-4 text-xl text-gray-600 dark:text-gray-400">Chargement des FAQs...</p>
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Administration FAQ" />
            
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-8">
                        <div className="flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                                    🛠️ Administration FAQ
                                </h1>
                                <p className="mt-2 text-gray-600 dark:text-gray-400">
                                    Gérez les questions et réponses du chatbot Jadara
                                </p>
                            </div>
                            <div className="flex items-center gap-4">
                                <div className="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <span className="text-sm text-gray-600 dark:text-gray-400">Total FAQs:</span>
                                    <span className="ml-2 font-bold text-blue-600 dark:text-blue-400">{faqs.length}</span>
                                </div>
                                <button
                                    onClick={() => {
                                        if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                                            window.location.href = '/chat';
                                        }
                                    }}
                                    className="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors"
                                    title="Retour au chat"
                                >
                                    🏠 Retour au chat
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Notification */}
                    {notification && (
                        <div className={`mb-6 p-4 rounded-lg ${
                            notification.type === 'error' 
                                ? 'bg-red-100 border border-red-300 text-red-700' 
                                : 'bg-green-100 border border-green-300 text-green-700'
                        }`}>
                            {notification.message}
                        </div>
                    )}

                    {/* Actions */}
                    <div className="mb-6 flex flex-col sm:flex-row gap-4 justify-between">
                        {/* Recherche */}
                        <div className="relative flex-1 max-w-md">
                            <input
                                type="text"
                                placeholder="🔍 Rechercher dans les FAQs..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="w-full pl-4 pr-10 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        {/* Bouton Ajouter */}
                        <button
                            onClick={() => setShowAddForm(!showAddForm)}
                            className="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                        >
                            <span className="text-lg">+</span>
                            Ajouter une FAQ
                        </button>
                    </div>

                    {/* Formulaire d'ajout */}
                    {showAddForm && (
                        <div className="mb-8 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                ➕ Nouvelle FAQ
                            </h3>
                            <form onSubmit={handleAddFaq} className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Question
                                    </label>
                                    <input
                                        type="text"
                                        value={newFaq.question}
                                        onChange={(e) => setNewFaq({ ...newFaq, question: e.target.value })}
                                        className="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Saisissez la question..."
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Réponse
                                    </label>
                                    <textarea
                                        value={newFaq.answer}
                                        onChange={(e) => setNewFaq({ ...newFaq, answer: e.target.value })}
                                        rows={4}
                                        className="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Saisissez la réponse..."
                                        required
                                    />
                                </div>
                                <div className="flex gap-3">
                                    <button
                                        type="submit"
                                        className="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors"
                                    >
                                        ✅ Ajouter
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setShowAddForm(false);
                                            setNewFaq({ question: '', answer: '' });
                                        }}
                                        className="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors"
                                    >
                                        ❌ Annuler
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}

                    {/* Liste des FAQs */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        {loading ? (
                            <div className="p-8 text-center">
                                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                                <p className="text-gray-600 dark:text-gray-400">Chargement des FAQs...</p>
                            </div>
                        ) : filteredFaqs.length === 0 ? (
                            <div className="p-8 text-center">
                                <div className="text-6xl mb-4">🤷‍♂️</div>
                                <p className="text-gray-600 dark:text-gray-400">
                                    {searchTerm ? 'Aucune FAQ trouvée pour cette recherche' : 'Aucune FAQ disponible'}
                                </p>
                            </div>
                        ) : (
                            <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                {filteredFaqs.map((faq, index) => (
                                    <FaqItem
                                        key={faq.id}
                                        faq={faq}
                                        index={index}
                                        isEditing={editingId === faq.id}
                                        onEdit={setEditingId}
                                        onUpdate={handleUpdateFaq}
                                        onDelete={handleDeleteFaq}
                                    />
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Footer */}
                    <div className="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <p>Interface d'administration FAQ - Chatbot Jadara</p>
                    </div>
                </div>
            </div>
        </>
    );
}

// Composant pour chaque FAQ
function FaqItem({ faq, index, isEditing, onEdit, onUpdate, onDelete }) {
    const [editData, setEditData] = useState({ question: faq.question, answer: faq.answer });

    const handleSave = () => {
        if (!editData.question.trim() || !editData.answer.trim()) {
            alert('Veuillez remplir tous les champs');
            return;
        }
        onUpdate(faq.id, editData);
    };

    const handleCancel = () => {
        setEditData({ question: faq.question, answer: faq.answer });
        onEdit(null);
    };

    return (
        <div className="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div className="flex items-start justify-between">
                <div className="flex-1">
                    <div className="flex items-center gap-3 mb-3">
                        <span className="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-sm font-bold">
                            {index + 1}
                        </span>
                        <div className="text-xs text-gray-500 dark:text-gray-400">
                            ID: {faq.id} | Créé le {new Date(faq.created_at).toLocaleDateString('fr-FR')}
                        </div>
                    </div>

                    {isEditing ? (
                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Question
                                </label>
                                <input
                                    type="text"
                                    value={editData.question}
                                    onChange={(e) => setEditData({ ...editData, question: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Réponse
                                </label>
                                <textarea
                                    value={editData.answer}
                                    onChange={(e) => setEditData({ ...editData, answer: e.target.value })}
                                    rows={3}
                                    className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div className="flex gap-2">
                                <button
                                    onClick={handleSave}
                                    className="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors"
                                >
                                    ✅ Sauvegarder
                                </button>
                                <button
                                    onClick={handleCancel}
                                    className="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors"
                                >
                                    ❌ Annuler
                                </button>
                            </div>
                        </div>
                    ) : (
                        <div>
                            <div className="mb-3">
                                <h4 className="font-semibold text-gray-900 dark:text-white mb-2">
                                    ❓ {faq.question}
                                </h4>
                                <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                                    {faq.answer}
                                </p>
                            </div>
                        </div>
                    )}
                </div>

                {!isEditing && (
                    <div className="flex gap-2 ml-4">
                        <button
                            onClick={() => onEdit(faq.id)}
                            className="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg transition-colors"
                            title="Modifier"
                        >
                            ✏️
                        </button>
                        <button
                            onClick={() => onDelete(faq.id)}
                            className="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-lg transition-colors"
                            title="Supprimer"
                        >
                            🗑️
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}

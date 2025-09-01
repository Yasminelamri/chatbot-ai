import React, { useState } from 'react'
import { Head, useForm, router } from '@inertiajs/react'

export default function Management({ faqs, search, stats }) {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editingFaq, setEditingFaq] = useState(null)
  const [showImportModal, setShowImportModal] = useState(false)
  
  const { data, setData, post, put, processing, errors, reset } = useForm({
    question: '',
    answer: '',
  })

  const searchForm = useForm({
    search: search || '',
  })

  const importForm = useForm({
    csv_file: null,
  })

  const openModal = (faq = null) => {
    if (faq) {
      setEditingFaq(faq)
      setData({
        question: faq.question,
        answer: faq.answer,
      })
    } else {
      setEditingFaq(null)
      reset()
    }
    setIsModalOpen(true)
  }

  const closeModal = () => {
    setIsModalOpen(false)
    setEditingFaq(null)
    reset()
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    
    if (editingFaq) {
      put(`/faq/management/${editingFaq.id}`, {
        onSuccess: () => closeModal(),
      })
    } else {
      post('/faq/management', {
        onSuccess: () => closeModal(),
      })
    }
  }

  const handleDelete = (faq) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer cette question ?\n\n"${faq.question}"`)) {
      router.delete(`/faq/management/${faq.id}`)
    }
  }

  const handleSearch = (e) => {
    e.preventDefault()
    router.get('/faq/management', { search: searchForm.data.search })
  }

  const handleImport = (e) => {
    e.preventDefault()
    importForm.post('/faq/management/import', {
      onSuccess: () => {
        setShowImportModal(false)
        importForm.reset()
      }
    })
  }

  return (
    <>
      <Head title="Gestion FAQ - Jadara" />
      
      <div className="min-h-screen bg-gray-50">
        {/* Header */}
        <div className="bg-white shadow">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex justify-between items-center py-6">
              <div>
                <h1 className="text-3xl font-bold text-gray-900">📚 Gestion FAQ</h1>
                <p className="text-gray-600">Interface de gestion des questions-réponses du chatbot Jadara</p>
              </div>
              <div className="flex space-x-3">
                <button
                  onClick={() => router.get('/faq/management/export')}
                  className="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
                >
                  📥 Exporter CSV
                </button>
                <button
                  onClick={() => setShowImportModal(true)}
                  className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                >
                  📤 Importer CSV
                </button>
                <button
                  onClick={() => openModal()}
                  className="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors"
                >
                  ➕ Nouvelle Question
                </button>
              </div>
            </div>
          </div>
        </div>

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          {/* Statistiques */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div className="bg-white p-6 rounded-lg shadow">
              <div className="flex items-center">
                <div className="p-3 rounded-full bg-blue-100 text-blue-600">
                  📊
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Total Questions</p>
                  <p className="text-2xl font-bold text-gray-900">{stats.total}</p>
                </div>
              </div>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow">
              <div className="flex items-center">
                <div className="p-3 rounded-full bg-green-100 text-green-600">
                  🆕
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Ajoutées (7j)</p>
                  <p className="text-2xl font-bold text-gray-900">{stats.recent}</p>
                </div>
              </div>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow">
              <div className="flex items-center">
                <div className="p-3 rounded-full bg-orange-100 text-orange-600">
                  ✏️
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Modifiées (7j)</p>
                  <p className="text-2xl font-bold text-gray-900">{stats.updated}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Barre de recherche */}
          <div className="bg-white p-6 rounded-lg shadow mb-6">
            <form onSubmit={handleSearch} className="flex gap-4">
              <div className="flex-1">
                <input
                  type="text"
                  value={searchForm.data.search}
                  onChange={(e) => searchForm.setData('search', e.target.value)}
                  placeholder="Rechercher dans les questions et réponses..."
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                />
              </div>
              <button
                type="submit"
                disabled={searchForm.processing}
                className="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50"
              >
                🔍 Rechercher
              </button>
              {search && (
                <button
                  type="button"
                  onClick={() => router.get('/faq/management')}
                  className="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors"
                >
                  ❌ Effacer
                </button>
              )}
            </form>
          </div>

          {/* Liste des FAQ */}
          <div className="bg-white rounded-lg shadow">
            <div className="px-6 py-4 border-b border-gray-200">
              <h2 className="text-lg font-semibold text-gray-900">
                Questions FAQ ({faqs.total} résultats)
              </h2>
            </div>
            
            <div className="divide-y divide-gray-200">
              {faqs.data.map((faq) => (
                <div key={faq.id} className="p-6 hover:bg-gray-50">
                  <div className="flex justify-between items-start">
                    <div className="flex-1">
                      <h3 className="text-lg font-medium text-gray-900 mb-2">
                        ❓ {faq.question}
                      </h3>
                      <div className="text-gray-600 mb-3 whitespace-pre-line">
                        {faq.answer.length > 200 
                          ? faq.answer.substring(0, 200) + '...' 
                          : faq.answer
                        }
                      </div>
                      <div className="flex items-center text-sm text-gray-500 space-x-4">
                        <span>📅 Créé: {new Date(faq.created_at).toLocaleDateString('fr-FR')}</span>
                        <span>✏️ Modifié: {new Date(faq.updated_at).toLocaleDateString('fr-FR')}</span>
                      </div>
                    </div>
                    
                    <div className="ml-6 flex space-x-2">
                      <button
                        onClick={() => openModal(faq)}
                        className="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors"
                      >
                        ✏️ Modifier
                      </button>
                      <button
                        onClick={() => handleDelete(faq)}
                        className="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors"
                      >
                        🗑️ Supprimer
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* Pagination */}
            {faqs.links && faqs.links.length > 3 && (
              <div className="px-6 py-4 border-t border-gray-200">
                <div className="flex justify-center space-x-2">
                  {faqs.links.map((link, index) => (
                    <button
                      key={index}
                      onClick={() => link.url && router.get(link.url)}
                      disabled={!link.url}
                      className={`px-3 py-1 rounded text-sm transition-colors ${
                        link.active 
                          ? 'bg-purple-600 text-white' 
                          : link.url 
                            ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' 
                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      }`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Modal d'ajout/modification */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-semibold text-gray-900">
                {editingFaq ? '✏️ Modifier la question' : '➕ Nouvelle question'}
              </h2>
            </div>
            
            <form onSubmit={handleSubmit} className="p-6">
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Question *
                </label>
                <input
                  type="text"
                  value={data.question}
                  onChange={(e) => setData('question', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  placeholder="Ex: Comment prolonger ma bourse ?"
                  required
                />
                {errors.question && (
                  <p className="text-red-600 text-sm mt-1">{errors.question}</p>
                )}
              </div>
              
              <div className="mb-6">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Réponse *
                </label>
                <textarea
                  value={data.answer}
                  onChange={(e) => setData('answer', e.target.value)}
                  rows={8}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  placeholder="Réponse détaillée à la question..."
                  required
                />
                {errors.answer && (
                  <p className="text-red-600 text-sm mt-1">{errors.answer}</p>
                )}
              </div>
              
              <div className="flex justify-end space-x-3">
                <button
                  type="button"
                  onClick={closeModal}
                  className="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  disabled={processing}
                  className="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50"
                >
                  {processing ? 'Enregistrement...' : editingFaq ? 'Modifier' : 'Ajouter'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal d'import */}
      {showImportModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg max-w-md w-full">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-semibold text-gray-900">📤 Importer FAQ</h2>
            </div>
            
            <form onSubmit={handleImport} className="p-6">
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Fichier CSV *
                </label>
                <input
                  type="file"
                  accept=".csv,.txt"
                  onChange={(e) => importForm.setData('csv_file', e.target.files[0])}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  required
                />
                <p className="text-sm text-gray-500 mt-1">
                  Format: Question, Réponse (une ligne par question)
                </p>
                {importForm.errors.csv_file && (
                  <p className="text-red-600 text-sm mt-1">{importForm.errors.csv_file}</p>
                )}
              </div>
              
              <div className="flex justify-end space-x-3">
                <button
                  type="button"
                  onClick={() => setShowImportModal(false)}
                  className="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  disabled={importForm.processing}
                  className="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50"
                >
                  {importForm.processing ? 'Import...' : 'Importer'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  )
}

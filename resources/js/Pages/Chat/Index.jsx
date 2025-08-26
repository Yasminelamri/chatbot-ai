import { useForm, router } from '@inertiajs/react'
import { useEffect, useRef, useState } from 'react'

function ConfirmModal({ open, title = 'Confirmer', message, onCancel, onConfirm }) {
  if (!open) return null
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div className="relative w-full max-w-md mx-4">
        <div className="bg-white rounded-xl shadow-2xl border border-gray-200">
          <div className="px-6 py-4 border-b border-gray-200">
            <h3 className="text-lg font-semibold text-gray-900">{title}</h3>
          </div>
          <div className="px-6 py-4 text-gray-700 whitespace-pre-wrap">{message}</div>
          <div className="px-6 py-4 flex items-center justify-end gap-3 bg-gray-50 rounded-b-xl">
            <button onClick={onCancel} className="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
              Annuler
            </button>
            <button onClick={onConfirm} className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
              Confirmer
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

export default function Index({ messages, conversationId, conversations }) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

  const { data, setData, post, processing, reset } = useForm({
    message: '',
    image: null,
    audio: null,
    _token: csrfToken,
  })

  const fileImageRef = useRef(null)
  const bottomRef = useRef(null)
  const [darkMode, setDarkMode] = useState(false)
  const [confirm, setConfirm] = useState({ open: false, message: '', onConfirm: () => {} })
  const [editingMessageId, setEditingMessageId] = useState(null)
  const [editingMessageText, setEditingMessageText] = useState('')
  const [audioBlob, setAudioBlob] = useState(null)
  const [mediaRecorder, setMediaRecorder] = useState(null)
  const [isRecording, setIsRecording] = useState(false)
  const [showArchives, setShowArchives] = useState(false)
  const [searchQuery, setSearchQuery] = useState('')

  const quickReplies = [
    { text: '💰 Prolongation bourse', value: 'Comment demander une prolongation de ma bourse ?' },
    { text: '📅 Date versement', value: 'À quelle date la bourse est-elle versée ?' },
    { text: '📋 Documents annuels', value: 'Quels sont les documents à envoyer chaque année ?' },
    { text: '❌ Problème bourse', value: "Je n'ai pas encore reçu ma bourse ce mois-ci, que faire ?" },
    { text: '🏦 Changement RIB', value: 'Que faire si je change de compte bancaire ?' },
    { text: '📞 Contact urgent', value: "À qui dois-je m'adresser en cas de problème urgent ?" }
  ]

  useEffect(() => {
    document.documentElement.classList.toggle('dark', darkMode)
  }, [darkMode])

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages, conversationId])

  const handleSubmit = (e) => {
    e.preventDefault()
    if (processing) return
    if (!data.message.trim() && !data.image && !data.audio) return

    post(`/chat/send?c=${conversationId}`, {
      forceFormData: true,
      onSuccess: () => {
        reset()
        setAudioBlob(null)
        if(fileImageRef.current) fileImageRef.current.value = ''
      },
    })
  }

  const handleQuickReply = (value) => {
    setData('message', value)
  }

  const handlePickImage = (e) => {
    const file = e.target.files?.[0]
    if (file) setData('image', file)
  }

  // Fonctions pour gérer les conversations
  const archiveConversation = (id) => {
    const isCurrentlyArchived = conversations.find(c => c.id === id)?.archived
    const route = isCurrentlyArchived ? `/chat/conversations/${id}/unarchive` : `/chat/conversations/${id}/archive`
    
    router.post(route, {}, {
      onSuccess: () => {
        router.reload()
      }
    })
  }

  const deleteConversation = (id) => {
    setConfirm({
      open: true,
      title: 'Supprimer la conversation',
      message: 'Êtes-vous sûr de vouloir supprimer cette conversation ? Cette action est irréversible.',
      onConfirm: () => {
        router.delete(`/chat/conversations/${id}`, {
          onSuccess: () => {
            router.reload()
          }
        })
        setConfirm({ open: false, message: '', onConfirm: () => {} })
      }
    })
  }

  const deleteMessage = (id) => {
    router.delete(`/chat/messages/${id}`, {
      onSuccess: () => {
        router.reload()
      }
    })
    setConfirm({ open: false, message: '', onConfirm: () => {} })
  }

  const clearAll = () => {
    setConfirm({
      open: true,
      title: 'Vider l\'historique',
      message: 'Êtes-vous sûr de vouloir supprimer tous les messages ? Cette action est irréversible.',
      onConfirm: () => {
        router.post('/chat/clear', {
          onSuccess: () => {
            router.reload()
          }
        })
        setConfirm({ open: false, message: '', onConfirm: () => {} })
      }
    })
  }

  const filteredConversations = conversations.filter(c => {
    const matchesSearch = c.title?.toLowerCase().includes(searchQuery.toLowerCase()) || 
                         `conversation ${c.id}`.includes(searchQuery.toLowerCase())
    const matchesArchive = showArchives ? c.archived : !c.archived
    return matchesSearch && matchesArchive
  })

  const startRecording = async () => {
    if (!navigator.mediaDevices?.getUserMedia) return alert('Micro non disponible')
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    const recorder = new MediaRecorder(stream)
    const chunks = []
    recorder.ondataavailable = (ev) => chunks.push(ev.data)
    recorder.onstop = () => {
      const blob = new Blob(chunks, { type: 'audio/webm' })
      setAudioBlob(blob)
      setData('audio', new File([blob], 'voice.webm', { type: 'audio/webm' }))
      stream.getTracks().forEach((t) => t.stop())
    }
    recorder.start()
    setMediaRecorder(recorder)
    setIsRecording(true)
  }

  const stopRecording = () => {
    if(mediaRecorder && isRecording) { 
      mediaRecorder.stop(); 
      setIsRecording(false) 
    }
  }

  const updateMessage = async (id, content) => {
    const form = new FormData()
    form.append('_method', 'PUT')
    form.append('content', content)
    await fetch(`/chat/messages/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: form })
    setEditingMessageId(null)
    router.reload({ only: ['messages'] })
  }

  return (
    <div className={`min-h-screen ${darkMode ? 'dark bg-gray-900' : 'bg-gray-50'}`}>
      <ConfirmModal 
        open={confirm.open} 
        title={confirm.title}
        message={confirm.message} 
        onCancel={() => setConfirm({ open: false, title: '', message: '', onConfirm: () => {} })} 
        onConfirm={() => { confirm.onConfirm(); setConfirm({ open: false, title: '', message: '', onConfirm: () => {} }) }} 
      />

      <div className="flex h-screen">
        {/* Sidebar conversations */}
        <aside className="hidden lg:flex flex-col w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
          <div className="p-4 border-b border-gray-200 dark:border-gray-700">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-xl font-bold text-gray-900 dark:text-white">💬 Conversations</h2>
              <button 
                onClick={() => setDarkMode(!darkMode)} 
                className="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                title={darkMode ? 'Mode clair' : 'Mode sombre'}
              >
                {darkMode ? '☀️' : '🌙'}
              </button>
            </div>
            
            <div className="flex gap-2 mb-3">
              <button
                onClick={() => router.get('/chat/new')}
                className="flex-1 bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
              >
                ➕ Nouvelle
              </button>
              <button
                onClick={() => setShowArchives(!showArchives)}
                className={`px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                  showArchives 
                    ? 'bg-green-600 text-white hover:bg-green-700' 
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                }`}
              >
                {showArchives ? '📤 Actives' : '📦 Archives'}
              </button>
            </div>

            <div className="relative">
              <input 
                type="text" 
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="🔍 Rechercher..." 
                className="w-full px-3 py-2 pl-10 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span className="text-gray-400">🔍</span>
              </div>
            </div>
          </div>

          <div className="flex-1 overflow-y-auto">
            {filteredConversations.length === 0 ? (
              <div className="p-4 text-center text-gray-500 dark:text-gray-400">
                {showArchives ? 'Aucune conversation archivée' : 'Aucune conversation active'}
              </div>
            ) : (
              filteredConversations.map(c => (
                <div key={c.id} className={`p-3 border-b border-gray-200 dark:border-gray-700 transition-colors ${
                  Number(conversationId) === c.id 
                    ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-l-blue-500' 
                    : 'hover:bg-gray-50 dark:hover:bg-gray-700'
                }`}>
                  <div className="flex items-center justify-between">
                    <a 
                      href={`/chat?c=${c.id}`} 
                      className="flex-1 min-w-0 block mr-2"
                    >
                      <div className="text-sm font-medium truncate text-gray-900 dark:text-white">
                        {c.title || `Conversation ${c.id}`}
                      </div>
                      <div className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {new Date(c.updated_at).toLocaleString('fr-FR', { 
                          day: '2-digit', 
                          month: '2-digit', 
                          hour: '2-digit', 
                          minute: '2-digit' 
                        })}
                      </div>
                    </a>
                    
                    <div className="flex gap-1">
                      {!c.archived ? (
                        <button
                          onClick={() => archiveConversation(c.id)}
                          className="p-1.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                          title="Archiver"
                        >
                          📦
                        </button>
                      ) : (
                        <button
                          onClick={() => archiveConversation(c.id)}
                          className="p-1.5 rounded text-xs bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/30 transition-colors"
                          title="Désarchiver"
                        >
                          📤
                        </button>
                      )}
                      <button
                        onClick={() => deleteConversation(c.id)}
                        className="p-1.5 rounded text-xs bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/30 transition-colors"
                        title="Supprimer"
                      >
                        🗑️
                      </button>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        </aside>

        {/* Chat area */}
        <div className="flex-1 flex flex-col bg-white dark:bg-gray-900">
          {/* Header */}
          <div className="p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                  🤖
                </div>
                <div>
                  <h1 className="text-xl font-bold text-gray-900 dark:text-white">Jadara Assistant</h1>
                  <p className="text-sm text-gray-500 dark:text-gray-400">Votre guide pour l'impact social</p>
                </div>
              </div>
              
              <div className="flex items-center gap-2">
                <button
                  onClick={clearAll}
                  className="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                  title="Vider l'historique"
                >
                  🧹
                </button>
                <button
                  onClick={() => router.get('/chat/new')}
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
                >
                  ➕ Nouvelle
                </button>
              </div>
            </div>
          </div>

          {/* Messages */}
          <div className="flex-1 overflow-y-auto p-4 space-y-4">
            {messages.length === 0 ? (
              <div className="text-center py-12">
                <div className="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-3xl">
                  🤖
                </div>
                <h2 className="text-2xl font-bold mb-2 text-gray-900 dark:text-white">Bienvenue sur Jadara !</h2>
                <p className="text-lg text-gray-600 dark:text-gray-400">Je suis votre assistant personnel. Comment puis-je vous aider aujourd'hui ?</p>
              </div>
            ) : (
              messages.map(msg => {
                const isUser = msg.sender === 'user'
                return (
                  <div key={msg.id} className={`flex items-start gap-3 ${isUser ? 'justify-end' : 'justify-start'}`}>
                    {!isUser && (
                      <div className="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        🤖
                      </div>
                    )}
                    
                    <div className={`max-w-[70%] ${isUser ? 'order-2' : ''}`}>
                      <div className={`rounded-2xl px-4 py-3 ${
                        isUser 
                          ? 'bg-blue-600 text-white' 
                          : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 shadow-sm'
                      }`}>
                        {editingMessageId === msg.id ? (
                          <div className="flex items-center gap-2">
                            <input 
                              value={editingMessageText} 
                              onChange={(e) => setEditingMessageText(e.target.value)} 
                              className="flex-1 px-2 py-1 rounded border dark:bg-gray-800 dark:text-white border-gray-300 dark:border-gray-600" 
                            />
                            <button 
                              onClick={() => updateMessage(msg.id, editingMessageText)} 
                              className="px-2 py-1 bg-emerald-600 text-white rounded text-sm"
                            >
                              ✓
                            </button>
                            <button 
                              onClick={() => setEditingMessageId(null)} 
                              className="px-2 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-sm"
                            >
                              ✕
                            </button>
                          </div>
                        ) : (
                          <div 
                            className="whitespace-pre-wrap leading-relaxed break-words"
                            style={{ 
                              whiteSpace: 'pre-line',
                              wordBreak: 'break-word',
                              maxWidth: '100%'
                            }}
                          >
                            {msg.content}
                          </div>
                        )}
                        
                        {msg.image_path && (
                          <img src={`/storage/${msg.image_path}`} alt="Image envoyée" className="mt-3 max-h-64 rounded-lg border" />
                        )}
                        {msg.audio_path && (
                          <audio className="mt-3 w-full" src={`/storage/${msg.audio_path}`} controls />
                        )}
                        
                        <div className={`mt-2 text-xs ${
                          isUser ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400'
                        }`}>
                          {new Date(msg.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
                        </div>
                      </div>
                      
                      {isUser && !editingMessageId && (
                        <div className="flex gap-1 mt-1 justify-end">
                          <button 
                            type="button" 
                            className="p-1 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" 
                            title="Éditer" 
                            onClick={() => { setEditingMessageId(msg.id); setEditingMessageText(msg.content || '') }}
                          >
                            ✏️
                          </button>
                          <button 
                            type="button" 
                            className="p-1 rounded text-xs bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/30 transition-colors" 
                            title="Supprimer" 
                            onClick={() => setConfirm({
                              open: true,
                              title: 'Supprimer le message',
                              message: 'Êtes-vous sûr de vouloir supprimer ce message ?',
                              onConfirm: () => deleteMessage(msg.id)
                            })}
                          >
                            🗑️
                          </button>
                        </div>
                      )}
                    </div>
                    
                    {isUser && (
                      <div className="w-8 h-8 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        👤
                      </div>
                    )}
                  </div>
                )
              })
            )}
            <div ref={bottomRef} />
          </div>

          {/* Quick replies */}
          {messages.length > 0 && (
            <div className="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
              <div className="flex flex-wrap gap-2">
                {quickReplies.map((q, i) => (
                  <button 
                    key={i} 
                    onClick={() => handleQuickReply(q.value)} 
                    className="px-3 py-2 rounded-full bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 text-sm font-medium transition-colors"
                  >
                    {q.text}
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Composer */}
          <form onSubmit={handleSubmit} className="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div className="flex items-end gap-3">
              <div className="flex-1 relative">
                <input 
                  type="text" 
                  value={data.message} 
                  onChange={(e) => setData('message', e.target.value)} 
                  placeholder="Tapez votre message..." 
                  className="w-full px-4 py-3 rounded-2xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                />
              </div>
              
              <input ref={fileImageRef} type="file" accept="image/*" className="hidden" onChange={handlePickImage} />
              <button 
                type="button" 
                onClick={() => fileImageRef.current?.click()} 
                className="p-3 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                title="Joindre une image"
              >
                📎
              </button>
              
              {!isRecording ? (
                <button 
                  type="button" 
                  onClick={startRecording} 
                  className="p-3 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                  title="Enregistrer un message vocal"
                >
                  🎤
                </button>
              ) : (
                <button 
                  type="button" 
                  onClick={stopRecording} 
                  className="p-3 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors"
                  title="Arrêter l'enregistrement"
                >
                  ⏹️
                </button>
              )}
              
              <button 
                type="submit" 
                disabled={processing || (!data.message.trim() && !data.image && !data.audio)} 
                className={`p-3 rounded-full transition-colors ${
                  processing || (!data.message.trim() && !data.image && !data.audio) 
                    ? 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed' 
                    : 'bg-blue-600 text-white hover:bg-blue-700'
                }`}
                title="Envoyer"
              >
                ✈️
              </button>
            </div>
            
            {(data.image || audioBlob) && (
              <div className="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mt-3">
                {data.image && <span>📷 Image: {data.image.name}</span>}
                {audioBlob && <span>🎤 Audio: {Math.round(audioBlob.size/1024)} Ko</span>}
              </div>
            )}
          </form>
        </div>
      </div>
    </div>
  )
}

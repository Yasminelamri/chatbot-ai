import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import './bootstrap'
import '../css/app.css'

// Import manuel des pages pour compatibilité avec Laravel Mix
import ChatIndex from './Pages/Chat/Index.jsx'

createInertiaApp({
  resolve: (name) => {
    // Mapping manuel des pages
    const pages = {
      'Chat/Index': ChatIndex,
    }
    
    return pages[name]
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />)
  },
})



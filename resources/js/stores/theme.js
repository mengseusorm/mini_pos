import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const THEME_COLORS = [
  { name: 'zinc',   label: 'Zinc'   },
  { name: 'slate',  label: 'Slate'  },
  { name: 'stone',  label: 'Stone'  },
  { name: 'gray',   label: 'Gray'   },
  { name: 'red',    label: 'Red'    },
  { name: 'rose',   label: 'Rose'   },
  { name: 'orange', label: 'Orange' },
  { name: 'amber',  label: 'Amber'  },
  { name: 'yellow', label: 'Yellow' },
  { name: 'lime',   label: 'Lime'   },
  { name: 'green',  label: 'Green'  },
  { name: 'teal',   label: 'Teal'   },
  { name: 'cyan',   label: 'Cyan'   },
  { name: 'sky',    label: 'Sky'    },
  { name: 'blue',   label: 'Blue'   },
  { name: 'indigo', label: 'Indigo' },
  { name: 'violet', label: 'Violet' },
  { name: 'purple', label: 'Purple' },
  { name: 'pink',   label: 'Pink'   },
]

export const useThemeStore = defineStore('theme', () => {
  const dark       = ref(localStorage.getItem('theme-dark') === 'true')
  const themeColor = ref(localStorage.getItem('theme-color') || 'zinc')

  function applyDark(value) {
    if (value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  function applyColor(color) {
    // Remove all previous theme-* classes
    document.documentElement.classList.forEach(cls => {
      if (cls.startsWith('theme-')) {
        document.documentElement.classList.remove(cls)
      }
    })
    document.documentElement.classList.add(`theme-${color}`)
  }

  function toggleDark() {
    dark.value = !dark.value
  }

  function setColor(color) {
    themeColor.value = color
  }

  // Persist + apply on change
  watch(dark, (val) => {
    localStorage.setItem('theme-dark', String(val))
    applyDark(val)
  })

  watch(themeColor, (val) => {
    localStorage.setItem('theme-color', val)
    applyColor(val)
  })

  // Initialize on mount
  applyDark(dark.value)
  applyColor(themeColor.value)

  return { dark, themeColor, toggleDark, setColor }
})

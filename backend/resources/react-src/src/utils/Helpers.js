/**
 * ============================================
 * UTILITIES - Funciones Auxiliares
 * ============================================
 */

// Formatear dinero colombiano
export const formatMoney = (amount) => {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

// Formatear fecha
export const formatDate = (date) => {
  return new Intl.DateTimeFormat('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(date));
};

// Formatear fecha corta
export const formatDateShort = (date) => {
  return new Intl.DateTimeFormat('es-CO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(new Date(date));
};

// Formatear hora
export const formatTime = (date) => {
  return new Intl.DateTimeFormat('es-CO', {
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(date));
};

// Calcular días vencidos
export const diasVencidos = (fechaVencimiento) => {
  const hoy = new Date();
  const vencimiento = new Date(fechaVencimiento);
  const diff = Math.floor((hoy - vencimiento) / (1000 * 60 * 60 * 24));
  return diff > 0 ? diff : 0;
};

// Verificar si está vencida
export const estaVencida = (fechaVencimiento) => {
  return new Date(fechaVencimiento) < new Date();
};

// Obtener color según estado
export const getEstadoColor = (estado) => {
  const colores = {
    PENDIENTE: 'bg-yellow-100 text-yellow-800',
    PAGADA: 'bg-green-100 text-green-800',
    VENCIDA: 'bg-red-100 text-red-800',
    ACTIVA: 'bg-blue-100 text-blue-800',
    BLOQUEADA: 'bg-gray-100 text-gray-800',
    CANCELADA: 'bg-red-100 text-red-800',
  };
  return colores[estado] || 'bg-gray-100 text-gray-800';
};

// Obtener nombre del día
export const getNombreDia = (numero) => {
  const dias = {
    1: 'Lunes',
    2: 'Martes',
    3: 'Miércoles',
    4: 'Jueves',
    5: 'Viernes',
    6: 'Sábado',
    7: 'Domingo',
  };
  return dias[numero] || '';
};

// Calcular distancia GPS (Haversine)
export const calcularDistancia = (lat1, lon1, lat2, lon2) => {
  const R = 6371; // Radio de la Tierra en km
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLon = ((lon2 - lon1) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return (R * c).toFixed(2);
};

// Truncar texto
export const truncate = (text, length = 50) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

// Validar email
export const isValidEmail = (email) => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
};

// Formatear número de teléfono
export const formatPhone = (phone) => {
  if (!phone) return '';
  const cleaned = phone.replace(/\D/g, '');
  if (cleaned.length === 10) {
    return `${cleaned.slice(0, 3)} ${cleaned.slice(3, 6)} ${cleaned.slice(6)}`;
  }
  return phone;
};

// Detectar si es móvil
export const isMobile = () => {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
    navigator.userAgent
  );
};

// Obtener ubicación actual
export const getCurrentLocation = () => {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Geolocalización no soportada'));
    } else {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          resolve({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
          });
        },
        (error) => {
          reject(error);
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0,
        }
      );
    }
  });
};

// Debounce
export const debounce = (func, wait) => {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
};

// Storage helpers
export const storage = {
  set: (key, value) => {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
      console.error('Error guardando en localStorage:', e);
    }
  },
  get: (key) => {
    try {
      const item = localStorage.getItem(key);
      return item ? JSON.parse(item) : null;
    } catch (e) {
      console.error('Error leyendo de localStorage:', e);
      return null;
    }
  },
  remove: (key) => {
    try {
      localStorage.removeItem(key);
    } catch (e) {
      console.error('Error eliminando de localStorage:', e);
    }
  },
};
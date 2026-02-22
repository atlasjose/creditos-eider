/**
 * Componente Loading - Spinner de carga optimizado para móvil
 */
const Loading = ({ fullScreen = false, message = 'Cargando...' }) => {
  if (fullScreen) {
    return (
      <div className="fixed inset-0 bg-gradient-to-br from-blue-500 to-purple-600 flex flex-col items-center justify-center z-50">
        <div className="animate-spin rounded-full h-16 w-16 border-4 border-white border-t-transparent"></div>
        <p className="text-white mt-4 text-lg font-medium">{message}</p>
      </div>
    );
  }

  return (
    <div className="flex flex-col items-center justify-center p-8">
      <div className="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
      <p className="text-gray-600 mt-3 text-sm">{message}</p>
    </div>
  );
};

export default Loading;
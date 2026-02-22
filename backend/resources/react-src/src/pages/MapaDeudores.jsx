import { useState, useEffect } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import { getDeudores, getDeudoresCercanos } from '../services/api';
import { useGeolocation } from '../hooks/useGeolocation';
import { formatPhone } from '../utils/helpers';
import Loading from '../components/Common/Loading';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix Leaflet icon
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

function ChangeView({ center, zoom }) {
  const map = useMap();
  map.setView(center, zoom);
  return null;
}

const MapaDeudores = () => {
  const [deudores, setDeudores] = useState([]);
  const [loading, setLoading] = useState(true);
  const [center, setCenter] = useState([4.710989, -74.072092]);
  const [showNearby, setShowNearby] = useState(false);
  const [radioKm, setRadioKm] = useState(5);
  const { location, loading: gpsLoading, getLocation } = useGeolocation();

  useEffect(() => {
    loadDeudores();
  }, []);

  useEffect(() => {
    if (location) {
      setCenter([location.latitude, location.longitude]);
      if (showNearby) {
        loadDeudoresCercanos();
      }
    }
  }, [location, showNearby, radioKm]);

  const loadDeudores = async () => {
    try {
      setLoading(true);
      const response = await getDeudores();
      const deudoresConGPS = (response.data.data || []).filter(
        (d) => d.latitud && d.longitud
      );
      setDeudores(deudoresConGPS);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const loadDeudoresCercanos = async () => {
    if (!location) return;
    try {
      setLoading(true);
      const response = await getDeudoresCercanos(
        location.latitude,
        location.longitude,
        radioKm
      );
      setDeudores(response.data.data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  if (loading && deudores.length === 0) {
    return <Loading fullScreen message="Cargando mapa..." />;
  }

  return (
    <div className="h-screen flex flex-col">
      <header className="bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg z-10">
        <div className="px-4 py-4">
          <div className="flex items-center justify-between">
            <h1 className="text-2xl font-bold">Mapa GPS</h1>
            <button
              onClick={() => window.history.back()}
              className="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg"
            >
              ← Volver
            </button>
          </div>
          <div className="mt-2 text-sm text-blue-100">
            {deudores.length} deudores con GPS
          </div>
        </div>
      </header>

      <div className="bg-white shadow p-4 z-10">
        <div className="flex gap-2">
          <button
            onClick={getLocation}
            disabled={gpsLoading}
            className="flex-1 bg-blue-500 text-white px-4 py-2 rounded-lg disabled:opacity-50"
          >
            {gpsLoading ? '📍 Obteniendo...' : '📍 Mi Ubicación'}
          </button>
          <button
            onClick={() => setShowNearby(!showNearby)}
            className={`flex-1 px-4 py-2 rounded-lg ${
              showNearby ? 'bg-green-500 text-white' : 'bg-gray-200'
            }`}
          >
            {showNearby ? '✓ Cercanos' : 'Ver Cercanos'}
          </button>
        </div>
        {showNearby && (
          <select
            value={radioKm}
            onChange={(e) => setRadioKm(Number(e.target.value))}
            className="w-full mt-2 px-3 py-2 border rounded-lg"
          >
            <option value={1}>1 km</option>
            <option value={3}>3 km</option>
            <option value={5}>5 km</option>
            <option value={10}>10 km</option>
          </select>
        )}
      </div>

      <div className="flex-1">
        <MapContainer
          center={center}
          zoom={13}
          style={{ height: '100%', width: '100%' }}
        >
          <ChangeView center={center} zoom={13} />
          <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />

          {location && (
            <Marker position={[location.latitude, location.longitude]}>
              <Popup>📍 Mi Ubicación</Popup>
            </Marker>
          )}

          {deudores.map((d) => (
            <Marker key={d.id_deudor} position={[d.latitud, d.longitud]}>
              <Popup>
                <strong>{d.nombres} {d.apellidos}</strong>
                <p>📞 {formatPhone(d.telefono_principal)}</p>
                <p>📍 {d.direccion}</p>
                {d.distancia_km && <p>📏 {d.distancia_km} km</p>}
              </Popup>
            </Marker>
          ))}
        </MapContainer>
      </div>
    </div>
  );
};

export default MapaDeudores;
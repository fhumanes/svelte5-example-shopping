import { writable } from 'svelte/store';
import { 
  getAuth, setAuth, clearAuth,
  getToken, setToken, clearToken,
  getGroup, setGroup, clearGroup
      } from '../lib/utils.js';  


/* -------------------------------------------------------
   AUTH STORE
------------------------------------------------------- */

// Leer desde localStorage al iniciar "auth"
const storedAuth = getAuth();
const initialAuth = storedAuth
  ? JSON.parse(storedAuth)
  : { isAuthenticated: false, user: null };
if (!initialAuth.isAuthenticated) {
  clearToken();  // Si no está autenticado, limpiamos el token
}
export const auth = writable(initialAuth);

// Guardar automáticamente en localStorage
auth.subscribe(value => {
  setAuth(JSON.stringify(value));
});

// Iniciar sesión
export function login(token, dataUser) {
  auth.set({ isAuthenticated: true, user: dataUser });
  setToken(token);
  return true;
}

// Cerrar sesión
export function logout() {
  auth.set({ isAuthenticated: false, user: null });
  clearToken();
  clearAuth();
  group.set(null);   // limpiamos el store
  clearGroup();      // limpiamos localStorage
  return true;
}



/* -------------------------------------------------------
   GROUP STORE (JSON puro)
------------------------------------------------------- */

// Leer desde localStorage al iniciar "group"
const storedGroup = getGroup();
const initialGroup = storedGroup ? JSON.parse(storedGroup) : null;

// El store contiene directamente el JSON del grupo
export const group = writable(initialGroup);

// Guardar automáticamente en localStorage
group.subscribe(value => {
  if (value) {
    setGroup(JSON.stringify(value));  // guardamos el JSON tal cual
  } else {
    clearGroup();                     // si es null, borramos
  }
});

// Establecer el grupo activo (JSON puro)
export function setActiveGroup(groupData) {
  group.set(groupData);
}

// Actualizar parcialmente el grupo
export function updateActiveGroup(partialData) {
  group.update(g => ({ ...g, ...partialData }));
}

// Limpiar el grupo
export function clearActiveGroup() {
  group.set(null);
  clearGroup();
}

// src/lib/utils.js

// Funciones de utilidad para el manejo de fechas, si las necesitas en otros lugares
export function toValidAPIDate(input) {
  const date = (input instanceof Date) ? input : new Date(input);
  return formatDateToAPI(date);
}

export function formatDateToAPI(dateObj) {
  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, "0");
  const day = String(dateObj.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

export function formatDateTimeToAPI(dateObj) {
  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, "0");
  const day = String(dateObj.getDate()).padStart(2, "0");

  const hours = String(dateObj.getHours()).padStart(2, "0");
  const minutes = String(dateObj.getMinutes()).padStart(2, "0");
  const seconds = String(dateObj.getSeconds()).padStart(2, "0");

  // console.log("formatDateTimeToAPI - dateObj: ", dateObj);
  // console.log("formatDateTimeToAPI - return: ", `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`);

  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

/**
 * Convierte una cadena de fecha de MySQL (yyyy-mm-dd) a un objeto Date
 * sin el desplazamiento horario de la zona local.
 * * @param {string} mysqlDate La cadena de fecha en formato 'yyyy-mm-dd'.
 * @returns {Date} Un objeto Date que representa la fecha.
 */
export function createDateFromMySQL(mysqlDatetime) {
// console.log("createDateFromMySQL - mysqlDatetime:", mysqlDatetime);
const date = new Date(mysqlDatetime.replace(" ", "T")); 
// return date.toLocaleString("es-ES"); 

// console.log("createDateFromMySQL - mysqlDatetime:", mysqlDatetime);
// console.log("createDateFromMySQL - date:", date);

return date;
}

/**
 * Convierte un objeto Date en una cadena de texto con formato dd-mm-yyyy.
 * @param {Date} dateObject El objeto Date a formatear.
 * @returns {string} La fecha en formato dd-mm-yyyy.
 */
export function formatDateToDDMMYYYY(date) {
  // console.log("formatDateToDDMMYYYY - date:", date);  
  const dia = String(date.getDate()).padStart(2, "0"); 
  const mes = String(date.getMonth() + 1).padStart(2, "0"); // meses 0–11 
  const año = date.getFullYear(); 
  return `${dia}/${mes}/${año}`;
}
/**
 * Accede al contenido de 'token-user' en localStorage.
 */
export function getToken() {
  return localStorage.getItem('token-user') || '';
}
/**
 * Almacena el contenido de 'token-user' en localStorage.
 */
export function setToken(token) {
  localStorage.setItem('token-user', token);
  return true
}
/**
 * Borra el contenido de 'token-user' en localStorage.
 */
export function clearToken() {
  localStorage.removeItem('token-user');
  return true
}
/**
 * Accede al contenido de 'auth' en localStorage.
 */
export function getAuth() {
  return localStorage.getItem('auth') || '';
}
/**
 * Almacena el contenido de 'auth' en localStorage.
 */
export function setAuth(userJson) {
  localStorage.setItem('auth', userJson);
  return true
}
/**
 * Borra el contenido de 'auth' en localStorage.
 */
export function clearAuth() {
  localStorage.removeItem('auth');
  return true
}
/**
 * Accede al contenido de 'group' en localStorage.
 */
export function getGroup() {
  return localStorage.getItem('group') || '';
}
/**
 * Almacena el contenido de 'group' en localStorage.
 */
export function setGroup(groupJson) {
  localStorage.setItem('group', groupJson);
  return true
}
/**
 * Borra el contenido de 'group' en localStorage.
 */
export function clearGroup() {
  localStorage.removeItem('group');
  return true
}









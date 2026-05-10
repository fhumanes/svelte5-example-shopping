// src/lib/api.js
import axios from 'axios';
import { APP_CONFIG } from './config.js';

let appHost = window.location.hostname;
// console.log("Conectado al Host:", appHost);

const API_BASE_URL = appHost === 'localhost' 
    ? APP_CONFIG.api.baseUrl.localhost 
    : APP_CONFIG.api.baseUrl.production;

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

// 👈 INTERCEPTOR SIMPLE (3 líneas)
api.interceptors.request.use((config) => {
    // console.log("API Request Config:", config);
    const tokenUser = localStorage.getItem('token-user');
    
    if (!tokenUser) {
        config.headers.Authorization = APP_CONFIG.auth.authorization.apiKey;
    } else {
        config.headers['token-user'] = tokenUser;
    }
    // console.log("API Request AFTER Config:", config);
    return config;
});

export default api;

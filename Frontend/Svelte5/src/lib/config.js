// src/lib/config.js

export const APP_CONFIG = {
    api: {
        baseUrl: {
            localhost: 'http://localhost/shopping-server/v1',
            production: 'https://fhumanes.com/shopping-server/v1'
        }
    },
    auth: {
        // 👈 Tokens fijos (fallback)
        authorization: {
            bearer: '',
            apiKey: '61437cfc-caa5-4cf9-9bee-85fe47efb09a',
            basic: 'Basic dXNlcjpwYXNz',
        },

    }
};

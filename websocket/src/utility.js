const axios = require('axios');

async function validateSanctumToken(token) {
    try {
        const response = await axios.get(`${process.env.API_URL}/api/profile`, {
            headers: { 
                ContentType: 'application/json',
                Authorization: `Bearer ${token}` }
        });
        return [response.status === 200, response.data.id];
    } catch (e) {
        console.error('Token validation error:', e.message);
        if (e.response) {
            console.error('Response data:', e.response.data);
            console.error('Response status:', e.response.status);
        }
        if (e.code) {
            console.error('Error code:', e.code);
        }
        if (e.request) {
            console.error('Request data:', e.request);
        }
        return [false, null];
    }
}

module.exports = { validateSanctumToken };
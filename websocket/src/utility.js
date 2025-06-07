const axios = require('axios');

async function validateSanctumToken(token) {
    try {
        const response = await axios.get(`${process.env.API_URL}/api/files`, {
            headers: { 
                ContentType: 'application/json',
                Authorization: `Bearer ${token}` }
        });
        return response.status === 200;
    } catch (e) {
        console.error('Token validation error:', e);
        return false;
    }
}

module.exports = { validateSanctumToken };
const API_BASE = '/api';

export const api = {
    async getOrganizations() {
        const response = await fetch(`${API_BASE}/organizations`);
        return await response.json();
    },

    async getHouses() {
        const response = await fetch(`${API_BASE}/buildings`);
        const buildings = await response.json();

        // Преобразуем buildings в houses формат
        return buildings.map(building => ({
            id: building.id,
            address: this.formatBuildingAddress(building),
            organization: building.organization, // нужно добавить связь в бэкенде
            hotWaterStatus: 'connected',
            heatingStatus: 'connected',
            coldWaterStatus: 'connected'
        }));
    },

    async getOutages() {
        const response = await fetch(`${API_BASE}/blackouts`);
        const blackouts = await response.json();
        return blackouts;
    },

    async getActiveOutages() {
        const response = await fetch(`${API_BASE}/blackouts/active`);
        return await response.json();
    },

    async getOutagesByType(type) {
        const response = await fetch(`${API_BASE}/blackouts/type/${type}`);
        return await response.json();
    },

    async searchOutages(query) {
        const response = await fetch(`${API_BASE}/blackouts/search?q=${encodeURIComponent(query)}`);
        return await response.json();
    },

    async getAddressSuggestions(query) {
        const buildings = await this.getHouses();
        const uniqueAddresses = [...new Set(buildings.map(building => building.address))];
        const suggestions = uniqueAddresses.filter(address =>
            address.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 5);
        return suggestions;
    },

    async getAnalytics(period) {
        const response = await fetch(`${API_BASE}/analytics/outages/${period}`);
        return await response.json();
    },

    formatBuildingAddress(building) {
        // Форматируем адрес из данных building
        const parts = [];
        if (building.street && building.street.name) parts.push(building.street.name);
        if (building.number) parts.push(building.number);
        return parts.join(', ');
    }
};
export default class Outage {
  constructor({ id, reason, status, houses, organization, startTime, endTime, outageType }) {
    this.id = id;
    this.reason = reason;
    this.status = status; // 'completed', 'active', 'planned'
    this.houses = houses || []; 
    this.organization = organization; 
    this.startTime = new Date(startTime); 
    this.endTime = new Date(endTime); 
    this.outageType = outageType; // 'electricity', 'cold_water', 'hot_water', 'heating'
  }

  get displayStatus() {
    const statusMap = {
      'completed': 'Завершено',
      'active': 'Актуально', 
      'planned': 'Ожидается'
    };
    return statusMap[this.status] || this.status;
  }

  get displayType() {
    const typeMap = {
      'electricity': 'Электричество',
      'cold_water': 'Холодная вода',
      'hot_water': 'Горячая вода',
      'heating': 'Отопление'
    };
    return typeMap[this.outageType] || this.outageType;
  }

  get markerColor() {
    const colorMap = {
      'cold_water': '#3498db',
      'hot_water': '#e74c3c',
      'electricity': '#f39c12',
      'heating': '#9b59b6'
    };
    return colorMap[this.outageType] || '#95a5a6';
  }
}
// src/models/Organization.js
export default class Organization {
  constructor({ id, name, serviceType, houses }) {
    this.id = id;
    this.name = name;
    this.serviceType = serviceType; // ['electricity', 'cold_water', 'hot_water', 'heating']
    this.houses = houses || [];
  }
}
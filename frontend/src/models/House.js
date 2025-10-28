// src/models/House.js
export default class House {
  constructor({ id, address, organization, hotWaterStatus, heatingStatus, coldWaterStatus }) {
    this.id = id;
    this.address = address;
    this.organization = organization; 
    this.hotWaterStatus = hotWaterStatus; 
    this.heatingStatus = heatingStatus; 
    this.coldWaterStatus = coldWaterStatus; 
  }
}
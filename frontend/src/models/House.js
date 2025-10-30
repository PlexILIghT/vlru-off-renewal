// src/models/House.js
export default class House {
  constructor({ id, address, initiatorName, hotWaterStatus, heatStatus, coldWaterStatus }) {
    this.id = id;
    this.address = address;
    this.organization = initiatorName;
    this.hotWaterStatus = hotWaterStatus; 
    this.heatStatus = heatStatus;
    this.coldWaterStatus = coldWaterStatus; 
  }
}
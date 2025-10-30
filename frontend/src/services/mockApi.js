import Organization from '@/models/Organization';
import Outage from '@/models/Outage';
import House from '@/models/House';

const organizationsData = [
  new Organization({
    id: 1,
    name: 'КГУП "Приморский водоканал"',
    serviceType: ['cold_water', 'hot_water']
  }),
  new Organization({
    id: 2,
    name: 'АО "Оборонэнерго"',
    serviceType: ['electricity']
  }),
  new Organization({
    id: 3,
    name: 'СП "Приморские тепловые сети" АО "ДГК"', 
    serviceType: ['heating']
  }),
  new Organization({
    id: 4,
    name: 'МУПВ ВПЭС',
    serviceType: ['electricity']
  }),
  new Organization({
    id: 5,
    name: 'Оборонэнерго',
    serviceType: ['electricity']
  })
];

const generateHouses = () => {
  const houses = [];
  const addresses = [
    'ул. Ленина, 15', 'пр-т Победы, 42', 'ул. Садовая, 7', 'ул. Морская, 123',
    'ул. Лесная, 65', 'ул. Центральная, 1', 'пр-т Строителей, 88', 'ул. Набережная, 33',
    'ул. Алеутская, 45', 'ул. Фонтанная, 21', 'ул. Океанский проспект, 30', 'ул. 1-я Морская, 12',
    'ул. Пологая, 66', 'ул. Калинина, 77', 'ул. Борисенко, 88', 'ул. Русская, 55', 'ул. Светланская, 78'
  ];

  for (let i = 1; i <= addresses.length; i++) {
    const organization = organizationsData[Math.floor(Math.random() * organizationsData.length)];
    
    houses.push(new House({
      id: i,
      address: addresses[i-1],
      organization: organization,
      hotWaterStatus: 'connected',
      heatingStatus: 'connected',
      coldWaterStatus: 'connected'
    }));
  }

  return houses;
};

const generateOutages = (houses) => {
  const outages = [];
  const reasons = [
    'Плановые ремонтные работы',
    'Устранение аварии',
    'Профилактические работы',
    'Подключение новых мощностей',
    'Реконструкция сетей'
  ];

  let outageId = 1;
  const now = new Date();

  
  const activeOutages = [
    {
      type: 'electricity',
      orgId: 2,
      houses: [houses[0], houses[1], houses[2]],
      start: new Date(now.getTime() - 2 * 60 * 60 * 1000),
      end: new Date(now.getTime() + 2 * 60 * 60 * 1000)
    },
    {
      type: 'cold_water', 
      orgId: 1,
      houses: [houses[3], houses[4], houses[5]],
      start: new Date(now.getTime() - 1 * 60 * 60 * 1000),
      end: new Date(now.getTime() + 3 * 60 * 60 * 1000)
    },
    {
      type: 'hot_water',
      orgId: 1, 
      houses: [houses[6], houses[7]],
      start: new Date(now.getTime() - 30 * 60 * 1000),
      end: new Date(now.getTime() + 4 * 60 * 60 * 1000)
    },
    {
      type: 'heating',
      orgId: 3,
      houses: [houses[8], houses[9], houses[10]],
      start: new Date(now.getTime() - 1.5 * 60 * 60 * 1000),
      end: new Date(now.getTime() + 2.5 * 60 * 60 * 1000)
    }
  ];

  activeOutages.forEach(outageData => {
    const organization = organizationsData.find(org => org.id === outageData.orgId);
    
    outages.push(new Outage({
      id: outageId++,
      reason: reasons[Math.floor(Math.random() * reasons.length)],
      status: 'active',
      houses: outageData.houses,
      organization: organization,
      startTime: outageData.start,
      endTime: outageData.end,
      outageType: outageData.type
    }));

    outageData.houses.forEach(house => {
      if (outageData.type === 'cold_water') house.coldWaterStatus = 'disconnected';
      if (outageData.type === 'hot_water') house.hotWaterStatus = 'disconnected';
      if (outageData.type === 'heating') house.heatingStatus = 'disconnected';
    });
  });

  for (let i = 0; i < 16; i++) {
    const type = ['electricity', 'cold_water', 'hot_water', 'heating'][Math.floor(Math.random() * 4)];
    const status = ['planned', 'completed', 'active'][Math.floor(Math.random() * 3)];
    const organization = organizationsData.find(org => org.serviceType.includes(type));
    
    const houseCount = Math.floor(Math.random() * 3) + 1;
    const affectedHouses = [];
    for (let j = 0; j < houseCount; j++) {
      affectedHouses.push(houses[Math.floor(Math.random() * houses.length)]);
    }

    let startTime, endTime;
    if (status === 'planned') {
      startTime = new Date(now.getTime() + (Math.floor(Math.random() * 3) + 1) * 24 * 60 * 60 * 1000);
      endTime = new Date(startTime.getTime() + (2 + Math.floor(Math.random() * 4)) * 60 * 60 * 1000);
    } else {
      startTime = new Date(now.getTime() - (Math.floor(Math.random() * 3) + 2) * 24 * 60 * 60 * 1000);
      endTime = new Date(startTime.getTime() + (2 + Math.floor(Math.random() * 4)) * 60 * 60 * 1000);
    }

    outages.push(new Outage({
      id: outageId++,
      reason: reasons[Math.floor(Math.random() * reasons.length)],
      status: status,
      houses: affectedHouses,
      organization: organization,
      startTime: startTime,
      endTime: endTime,
      outageType: type
    }));
  }

  return outages;
};

let houses = [];
let outages = [];

const initializeData = () => {
  houses = generateHouses();
  outages = generateOutages(houses);
  
  organizationsData.forEach(org => {
    org.houses = houses.filter(house => house.organization.id === org.id);
  });
};

export const mockApi = {
  initialize() {
    if (houses.length === 0) {
      initializeData();
    }
  },

  getOrganizations() {
    this.initialize();
    return Promise.resolve(organizationsData);
  },

  getHouses() {
    this.initialize();
    return Promise.resolve(houses);
  },

  getOutages() {
    this.initialize();
    return Promise.resolve(outages);
  },

  getActiveOutages() {
    this.initialize();
    return Promise.resolve(outages.filter(outage => outage.status === 'active'));
  },

  getOutagesByType(type) {
    this.initialize();
    return Promise.resolve(outages.filter(outage => outage.outageType === type));
  },

  searchOutages(query) {
    this.initialize();
    const results = outages.filter(outage => 
      outage.reason.toLowerCase().includes(query.toLowerCase()) ||
      outage.houses.some(house => house.address.toLowerCase().includes(query.toLowerCase()))
    );
    return Promise.resolve(results);
  },

  getAddressSuggestions(query) {
    this.initialize();
    const uniqueAddresses = [...new Set(houses.map(house => house.address))];
    const suggestions = uniqueAddresses.filter(address => 
      address.toLowerCase().includes(query.toLowerCase())
    ).slice(0, 5);
    return Promise.resolve(suggestions);
  },

  async getAnalytics(period) {
  this.initialize();
  
  const allOutages = outages;
  const now = new Date();
  let data = [];
  
  if (period === '60m') {
    // 12 интервалов по 5 минут
    for (let i = 0; i < 12; i++) {
      const intervalEnd = new Date(now.getTime() - (11 - i) * 5 * 60 * 1000);
      const intervalStart = new Date(intervalEnd.getTime() - 5 * 60 * 1000);
      
      // Включаем все отключения, которые были активны в этот интервал
      const periodOutages = allOutages.filter(outage => {
        const outageStart = new Date(outage.startTime);
        const outageEnd = new Date(outage.endTime);
        // Отключение активно если оно пересекается с интервалом
        return outageStart < intervalEnd && outageEnd > intervalStart;
      });
      
      // Рассчитываем количество домов по типам
      const typeHouses = {
        cold_water: periodOutages.filter(o => o.outageType === 'cold_water')
                         .reduce((sum, o) => sum + o.houses.length, 0),
        hot_water: periodOutages.filter(o => o.outageType === 'hot_water')
                        .reduce((sum, o) => sum + o.houses.length, 0),
        electricity: periodOutages.filter(o => o.outageType === 'electricity')
                          .reduce((sum, o) => sum + o.houses.length, 0),
        heating: periodOutages.filter(o => o.outageType === 'heating')
                      .reduce((sum, o) => sum + o.houses.length, 0)
      };

      data.push({
        label: intervalEnd.toLocaleTimeString('ru-RU', {
          hour: '2-digit',
          minute: '2-digit'
        }),
        types: typeHouses,
        total: periodOutages.length,
        affectedHouses: periodOutages.reduce((sum, o) => sum + o.houses.length, 0)
      });
    }
  } else if (period === '24h') {
    // 24 часа
    for (let i = 0; i < 24; i++) {
      const hourEnd = new Date(now.getTime() - (23 - i) * 60 * 60 * 1000);
      const hourStart = new Date(hourEnd.getTime() - 60 * 60 * 1000);
      
      // Включаем все отключения, которые были активны в этот час
      const periodOutages = allOutages.filter(outage => {
        const outageStart = new Date(outage.startTime);
        const outageEnd = new Date(outage.endTime);
        // Отключение активно если оно пересекается с часовым интервалом
        return outageStart < hourEnd && outageEnd > hourStart;
      });

      // Рассчитываем количество домов по типам
      const typeHouses = {
        cold_water: periodOutages.filter(o => o.outageType === 'cold_water')
                         .reduce((sum, o) => sum + o.houses.length, 0),
        hot_water: periodOutages.filter(o => o.outageType === 'hot_water')
                        .reduce((sum, o) => sum + o.houses.length, 0),
        electricity: periodOutages.filter(o => o.outageType === 'electricity')
                          .reduce((sum, o) => sum + o.houses.length, 0),
        heating: periodOutages.filter(o => o.outageType === 'heating')
                      .reduce((sum, o) => sum + o.houses.length, 0)
      };

      data.push({
        label: hourEnd.getHours().toString().padStart(2, '0') + ':00',
        types: typeHouses,
        total: periodOutages.length,
        affectedHouses: periodOutages.reduce((sum, o) => sum + o.houses.length, 0)
      });
    }
  } else if (period === '30d') {
    // 30 дней
    for (let i = 0; i < 30; i++) {
      const dayEnd = new Date(now.getTime() - (29 - i) * 24 * 60 * 60 * 1000);
      const dayStart = new Date(dayEnd.getTime() - 24 * 60 * 60 * 1000);
      
      // Включаем все отключения, которые были активны в этот день
      const periodOutages = allOutages.filter(outage => {
        const outageStart = new Date(outage.startTime);
        const outageEnd = new Date(outage.endTime);
        // Отключение активно если оно пересекается с дневным интервалом
        return outageStart < dayEnd && outageEnd > dayStart;
      });

      // Рассчитываем количество домов по типам
      const typeHouses = {
        cold_water: periodOutages.filter(o => o.outageType === 'cold_water')
                         .reduce((sum, o) => sum + o.houses.length, 0),
        hot_water: periodOutages.filter(o => o.outageType === 'hot_water')
                        .reduce((sum, o) => sum + o.houses.length, 0),
        electricity: periodOutages.filter(o => o.outageType === 'electricity')
                          .reduce((sum, o) => sum + o.houses.length, 0),
        heating: periodOutages.filter(o => o.outageType === 'heating')
                      .reduce((sum, o) => sum + o.houses.length, 0)
      };

      data.push({
        label: dayEnd.toLocaleDateString('ru-RU', {
          day: '2-digit',
          month: '2-digit'
        }),
        types: typeHouses,
        total: periodOutages.length,
        affectedHouses: periodOutages.reduce((sum, o) => sum + o.houses.length, 0)
      });
    }
  }
  
  return data;
}
};
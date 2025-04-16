import axios from 'axios';
import * as d3 from 'd3';

const API_URL = process.env.API_URL || 'http://localhost:8080/api.php';

const symbols = ['BTCUSD', 'ETHUSD', 'XRPUSD', 'LTCUSD', 'ADAUSD'];
const timeframes = ['1H', '4H', '1D', '1W'];
const positionTypes = ['Long', 'Short'];

function randomDate(start, end) {
  return new Date(start.getTime() + Math.random() * (end.getTime() - start.getTime()));
}

async function createMockTrade() {
  const tradeDate = randomDate(new Date(2023, 0, 1), new Date());
  const profitLoss = d3.randomUniform(-500, 1000)();

  try {
    await axios.post(API_URL, {
      symbol: symbols[Math.floor(Math.random() * symbols.length)],
      profit_loss: profitLoss.toFixed(2),
      trade_date: tradeDate.toISOString().split('T')[0],
      timeframe: timeframes[Math.floor(Math.random() * timeframes.length)],
      position_type: positionTypes[Math.floor(Math.random() * positionTypes.length)]
    });
  } catch (error) {
    console.error('Error creating trade:', error.message);
  }
}

async function populateData(numEntries = 100) {
  console.log(`Inserting ${numEntries} mock trades...`);
  
  for (let i = 0; i < numEntries; i++) {
    await createMockTrade();
    process.stdout.write(`\rProgress: ${((i + 1) / numEntries * 100).toFixed(1)}%`);
  }
  
  console.log('\nData population completed!');
}

// Start population
populateData(100).catch(console.error);
import requests
import random
from datetime import datetime, timedelta

API_URL = "http://localhost:8080/api.php"
NUM_TRADES = 100  # Change this number as needed

symbols = ['BTCUSD', 'ETHUSD', 'XRPUSD', 'LTCUSD', 'ADAUSD']
timeframes = ['1H', '4H', '1D', '1W']
position_types = ['Long', 'Short']

def random_date(start_date, end_date):
    time_between = end_date - start_date
    random_days = random.randrange(time_between.days)
    return start_date + timedelta(days=random_days)

def create_trade():
    try:
        # Generate random trade data
        trade_date = random_date(
            start_date=datetime(2023, 1, 1),
            end_date=datetime.now()
        ).strftime('%Y-%m-%d')
        
        payload = {
            "symbol": random.choice(symbols),
            "profit_loss": round(random.uniform(-500, 1000), 2),
            "trade_date": trade_date,
            "timeframe": random.choice(timeframes),
            "position_type": random.choice(position_types)
        }

        # Send POST request
        response = requests.post(
            API_URL,
            json=payload,
            headers={'Content-Type': 'application/json'}
        )
        
        if response.status_code != 200:
            print(f"\nError creating trade: {response.text}")
            
    except Exception as e:
        print(f"\nError: {str(e)}")

if __name__ == "__main__":
    print(f"Creating {NUM_TRADES} mock trades...")
    
    for i in range(NUM_TRADES):
        create_trade()
        print(f"\rProgress: {(i+1)/NUM_TRADES*100:.1f}%", end='', flush=True)
    
    print("\nDone! Check your dashboard.php")

# To test manually:
# curl -X POST http://localhost:8080/api.php -H "Content-Type: application/json" -d '{"symbol":"BTCUSD","profit_loss":250.5,"trade_date":"2024-03-01","timeframe":"1H","position_type":"Long"}'

#  curl -X POST http://localhost:8080/api.php \
#   -H "Content-Type: application/json" \
#   -d '{
#     "symbol": "BTCUSD",
#     "profit_loss": 250.50,
#     "trade_date": "2024-03-01",
#     "timeframe": "1H",
#     "position_type": "Long"
#   }'

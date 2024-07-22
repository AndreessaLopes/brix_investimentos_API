import yfinance as yf
import json

def fetch_stock_data():
    tickers = ['AAPL', 'MSFT', 'GOOGL', 'AMZN', 'TSLA', 'META', 'NFLX', 'NVDA', 'IBM', 'ORCL', 'INTC', 'AMD', 'CSCO', 'BA', 'DIS', 'V', 'MA', 'JPM', 'WMT', 'UNH', 'PG']
    data = []

    for ticker in tickers:
        try:
            stock = yf.Ticker(ticker)
            hist = stock.history(period="1d", interval="1m")
            for index, row in hist.iterrows():
                data.append({
                    "ticker": ticker,
                    "data_ativo": index.strftime('%d/%m/%Y, %H:%M:%S'),
                    "open": row['Open'],
                    "low": row['Low'],
                    "high": row['High'],
                    "close": row['Close'],
                    "volume": row['Volume']
                })
        except Exception as e:
            print(f"Error fetching data for {ticker}: {e}")

    return data

def main():
    data = fetch_stock_data()
    print(json.dumps(data, indent=4))

if __name__ == "__main__":
    main()

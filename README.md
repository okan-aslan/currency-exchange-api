# Currency Exchange API

Currency Exchange API is a RESTful API that allows you to retrieve exchange rate information and perform currency conversions. This API is a simple and effective tool for converting between different currencies.

## Features

-   **Exchange Rate Information:** Retrieve current exchange rate data.
-   **Currency Conversion:** Convert between different currencies.
-   **Updated Data:** Data is regularly updated.

## Installation

To set up the project, follow these steps:

1. **Clone the Repository:**

    ```bash
    git clone https://github.com/okan-aslann/currency-exchange-api.git
    ```

2. **Install Dependencies:**

    ```bash
    cd currency-exchange-api
    npm install
    ```

3. **Add Exchange Rate API Key:**

    This project uses [ExchangeRate-API](https://www.exchangerate-api.com/) for retrieving exchange rate data. You need to set up your API key in the `.env` file under the `EXCHANGE_RATE_API_KEY` variable.

    **Example `.env` file:**

    ```env
    EXCHANGE_RATE_API_KEY=your_api_key_here
    ```

## Usage

Here are examples of how to use the API:

### Retrieve Exchange Rate Information

**Endpoint:** `GET /api/rates`

**Description:** Fetch the current exchange rates for various currencies.

**Example Request:**

```bash
curl -X GET http://localhost:3000/api/rates
```

**Example Response:**

```json
{
  "USD": 1.0,
  "EUR": 0.84,
  "GBP": 0.75,
  ...
}
```

### Perform Currency Conversion

**Endpoint**: POST /api/convert

**Description**: Convert an amount from one currency to another.

**Request Body:**

```json
{
    "from": "USD",
    "to": "EUR",
    "amount": 100
}
```

**Example Request:**

```json
curl -X POST http://localhost:3000/api/convert \
  -H "Content-Type: application/json" \
  -d '{"from": "USD", "to": "EUR", "amount": 100}'
```

**Response:**

```json
{
    "from": "USD",
    "to": "EUR",
    "amount": 100,
    "convertedAmount": 84
}
```

![Screenshot 2024-08-18 at 7 31 23 PM](https://github.com/user-attachments/assets/f3dd368c-0950-47d2-b92b-7ba27a134520)

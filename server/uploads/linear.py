import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from sklearn.linear_model import LinearRegression
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, r2_score

# Load dataset
data_url = "https://github.com/SATYAMP023/AIML/raw/refs/heads/main/supermarket_sales.csv"
df = pd.read_csv(data_url)

# Display dataset structure
print(df.info())
print(df.head())

# Select relevant columns for regression (modify as needed)
# Example: Predicting 'Total' based on 'Quantity'
X = df[['Quantity']].values  # Independent variable
y = df['Total'].values  # Dependent variable

# Split data into training and testing sets
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Initialize and train the model
model = LinearRegression()
model.fit(X_train, y_train)

# Get model parameters
slope = model.coef_[0]
intercept = model.intercept_
print(f"Linear Regression Model: y = {slope:.5f} * X + {intercept:.2f}")

# Predictions
y_pred = model.predict(X_test)

# Model evaluation
mse = mean_squared_error(y_test, y_pred)
r2 = r2_score(y_test, y_pred)
print(f"Mean Squared Error: {mse:.2f}")
print(f"R-squared: {r2:.2f}")

# Visualization
plt.scatter(X_test, y_test, color='blue', alpha=0.5, label='Actual data')
plt.plot(X_test, y_pred, color='red', linewidth=2, label='Regression line')
plt.xlabel("Quantity")
plt.ylabel("Total")
plt.title("Linear Regression on Supermarket Sales Data")
plt.legend()
plt.show()

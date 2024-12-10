# Base image
FROM node:16 AS build

# Set working directory
WORKDIR /app

# Copy files
COPY package*.json ./
COPY . .

# Install dependencies
RUN npm install

# Expose port
EXPOSE 8080

# Run the application
CMD ["npm", "run", "serve"]

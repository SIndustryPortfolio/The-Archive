# Book Review Platform (Symfony + Google Books)

A **PHP Symfony** web application that allows users to **browse, review, and manage books** using the **Google Books API** as its primary data source.

The platform is built as a **versioned REST API**, designed for scalability, security, and clean separation between frontend and backend concerns.

---

## 📚 Overview

This project provides a fully-featured book browsing and review system with:

- API-first architecture
- Role-based user permissions
- Secure authentication
- External service integrations
- Efficient data handling via pagination

All functionality is exposed through a structured JSON-based REST API.

---

## 🧩 Architecture

- **Framework:** Symfony
- **API Style:** REST (JSON request/response)
- **Auth:** Session-based tokens (cookie authentication)
- **Database:** MySQL
- **External APIs:** Google Books, Google Gemini, Discord Webhooks, IP-API

---

## 🚀 Features

### 🔌 REST API (Versioned)

- All endpoints live under a versioned namespace (e.g. `/api/v1`)
- Resource & sub-resource structure
- JSON request bodies and responses
- Predictable HTTP status codes

**Example structure**
```text
/api/v1/books
/api/v1/books/{id}
/api/v1/books/{id}/reviews
/api/v1/users/{id}

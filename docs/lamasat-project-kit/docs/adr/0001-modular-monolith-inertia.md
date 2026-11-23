# ADR 0001 — Modular monolith with Inertia + Vue

**Status:** accepted · **Date:** 23/11/2026

**Context.** One clinic, ~10–20 staff users, strong coupling between front desk, clinical, pharmacy and billing; the team plans Laravel + Vue.

**Decision.** Single Laravel application with domain modules under `app/Domain`, Inertia.js 2 + Vue 3 + TypeScript for the UI, MySQL 8, Redis. No separate SPA/API in v1.

**Consequences.** Fast delivery with server-side validation and policies reused by the UI; a public API (Sanctum) can be added later for a patient or mobile app.

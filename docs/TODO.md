# Paganini — future work (v2+)

Items below are **not** part of the current release; tracked for planning only.

## v2 — weighted load balancing

- Support **weighted** instance selection (requires governance-side metadata: JSON, hash fields, or encoded weights in registration data).
- Likely needs a **strategy** or **configuration** flag on the resolver (out of scope for v1 uniform random / `index mod`).

## v2 — Redis SET + SRANDMEMBER registration backend

- Alternative to **GET + comma-separated string**:
  - Governance: `SADD service:foo host1:port1 host2:port2`
  - Read path: `SRANDMEMBER service:foo 1` for random pick, or combine with **`index mod`** after `SMEMBERS` / sorted member list (define stable ordering).
- **Migration**: existing comma-string keys must be migrated; resolver needs **storage mode** (string vs set) or separate implementation class.

## Pipeline — on hold

- **Redis Pipeline** (batch unrelated commands in one round-trip) is **not** on the roadmap until a concrete use case appears (e.g. mixed `GET` + other commands that cannot use `MGET`).
- Pure multi-key string reads remain covered by **`MGET`** in v1.

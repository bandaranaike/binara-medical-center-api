# Current Task

## Title

Add Electron billing service public API changes

## Status

Completed

## Goal

Support the Electron billing desk changes for service autocomplete, doctor billing defaults, split-price bill items, ad hoc services, and doctor-optional `others` billing through the public API.

## Work items

- add receptionist-safe service search under `/api/public/services/search`
- add doctor billing defaults under `/api/public/doctors/{doctor}/billing-config`
- extend public bill and booking payloads to accept structured split-price items
- persist bill item snapshots and referred amounts for Electron round trips
- support `others` requests by mapping them onto the existing backend treatment flow
- cover the updated public routes with feature tests

## Notes

- ad hoc item service records are created with unique dashed `services.key` values derived from the item name
- `doctor_id` is optional for public bill creation when the normalized service type is `treatment` / Electron `others`
- bill item snapshots now carry `service_name`, `service_key`, `referred_amount`, `doctor_id`, `category`, and `is_ad_hoc`

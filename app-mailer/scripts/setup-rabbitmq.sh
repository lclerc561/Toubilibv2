#!/bin/bash

sleep 5

# Exchange
curl -s -u guest:guest -X PUT \
  "http://localhost:15672/api/exchanges/%2F/toubilib.events" \
  -H "content-type: application/json" \
  -d '{"type":"topic","durable":true}'

# Queue
curl -s -u guest:guest -X PUT \
  "http://localhost:15672/api/queues/%2F/email.notifications" \
  -H "content-type: application/json" \
  -d '{"durable":true}'

# Bindings
curl -s -u guest:guest -X POST \
  "http://localhost:15672/api/bindings/%2F/e/toubilib.events/q/email.notifications" \
  -H "content-type: application/json" \
  -d '{"routing_key":"rdv.created.patient"}'

curl -s -u guest:guest -X POST \
  "http://localhost:15672/api/bindings/%2F/e/toubilib.events/q/email.notifications" \
  -H "content-type: application/json" \
  -d '{"routing_key":"rdv.created.praticien"}'

curl -s -u guest:guest -X POST \
  "http://localhost:15672/api/bindings/%2F/e/toubilib.events/q/email.notifications" \
  -H "content-type: application/json" \
  -d '{"routing_key":"rdv.cancelled.patient"}'

curl -s -u guest:guest -X POST \
  "http://localhost:15672/api/bindings/%2F/e/toubilib.events/q/email.notifications" \
  -H "content-type: application/json" \
  -d '{"routing_key":"rdv.cancelled.praticien"}'

echo "yeah"

# Asterisk Click-to-Call (PHP)

## Overview

**Asterisk Click-to-Call (PHP)** is a lightweight server-side script written in pure PHP that triggers outbound calls on an **Asterisk PBX** using the **Asterisk Manager Interface (AMI)** over a raw socket connection. The script authenticates against AMI and issues an `Originate` action to initiate a call from an internal extension to a target destination.

This implementation is intentionally minimalistic and synchronous, designed for environments that require low overhead, no external dependencies, and direct control over AMI signaling.

---

## Architecture

```
[PHP Script]
     |
     | TCP Socket (AMI / optional WS)
     v
[Asterisk Manager Interface]
     |
     | Action: Originate
     v
[Asterisk Dialplan]
     |
     v
[SIP Channel] -> Destination Extension
```

---

## Requirements

* PHP 7.x or higher
* Network access to the Asterisk server
* Asterisk with **AMI enabled** (`manager.conf`)
* A valid AMI user with `originate` permission
* Existing SIP extensions configured in Asterisk

---

## Dependencies

This project has **no external dependencies**.

* ❌ No JavaScript
* ❌ No PHP frameworks
* ❌ No third-party libraries

Only native PHP socket functions are used (`stream_socket_client`, `stream_socket_sendto`).

---

## Asterisk Configuration

### Enable AMI (`manager.conf`)

```ini
[general]
enabled = yes
port = 5038
bindaddr = 0.0.0.0

[user]
secret = secret
read = call,command,log,verbose
write = call,command
```

> ⚠️ **Security note:** Restrict AMI access by IP address whenever possible.

---

## Script Configuration

Before running the script, configure the following parameters directly in the PHP file:

* **Asterisk server IP** (`$ipServer`)
* **AMI port** (`$port`, default `5038`)
* **Transport protocol** (`tcp`)
* **AMI username and secret**
* **Internal SIP extension used as call origin**
* **Dialplan context**
* **Target extension**

Example:

```php
$ipServer = "172.0.0.1";
$port = "5038";
$protocolServer = "tcp";

$username = "user";
$password = "secret";

$internalPhoneline = "1003";
$target = "1006";

$context = "1-GRam-Grupo_1";
$ws = false; // Enable if AMI over WebSocket is configured
```

---

## Execution Flow

1. Open a socket connection to the Asterisk AMI
2. Send `Action: Login` with credentials
3. Validate authentication success
4. Send `Action: Originate` using `SIP/<extension>`
5. Asterisk processes the dialplan context
6. Call is originated asynchronously
7. Socket connection is closed

The script disables AMI events (`Events: off`) to reduce overhead and keep execution simple.

---

## AMI Originate Action (As Implemented)

```text
Action: Originate
Channel: SIP/1003
Callerid: Alisson
Exten: 1006
Context: 1-GRam-Grupo_1
Priority: 0
Async: yes
```

> ⚠️ **Note:** The `Priority` value must match the dialplan configuration defined in `extensions.conf`.

---

## WebSocket Support

The script optionally supports **AMI over WebSocket**. When enabled, the port is automatically suffixed with `/ws`:

```php
$ws = true;
```

This requires Asterisk to be properly configured to expose AMI via WebSocket.

---

## Error Handling

The script performs basic runtime checks:

* Socket connection failures
* AMI authentication errors
* Failed originate requests

Responses are validated by checking for the string `Success` in AMI responses. No retry or advanced error recovery logic is implemented.

---

## Security Considerations

* Do not expose this script publicly
* Restrict AMI access by IP
* Use strong AMI secrets
* Avoid hardcoding credentials in production environments
* Prefer running the script from a trusted backend service

---

## Limitations

* Single call per execution
* No call state tracking
* No event listening
* Synchronous execution
* CallerID is hardcoded in the script

These limitations are intentional to keep the implementation simple and predictable.

---

## Use Cases

* Internal click-to-call tools
* CRM integrations
* PBX automation scripts
* Legacy Asterisk environments

---

## Author

**Alisson Pelizaro**
Email: [alissonpelizaro@hotmail.com](mailto:alissonpelizaro@hotmail.com)

---

## License

This project is provided "as is", without warranty of any kind. Use at your own risk.

# repux-plugins

# Setup

To build docker environment execute the following command:
```
bin/exec up
```

Run shopify consumer to handle file requests:
```
bin/exec console rabbitmq:consumer shopify_store_process
```

Run amazon consumer to handle file requests:
```
bin/exec console rabbitmq:consumer amazon_channel_process
```

# API docs

Interactive API documentation is located under: http://localhost:8080/api/doc

# Development

Execute `bin/exec` script to display other helpful commands.

To be able to use PHP debugger please execute the following command:
```
sudo ifconfig en0 alias 10.254.254.254 255.255.255.0
```

...and setup PHPStorm with `10.254.254.254` as host for debugger and DBG proxy.

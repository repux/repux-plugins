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

# API docs

Interactive API documentation is located under: http://localhost:8080/api/doc

# Development

Execute `bin/exec` script to display other helpful commands.

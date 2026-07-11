<?php

it('registers no discovery routes while disabled (the default)', function () {
    $this->get('/.well-known/mcp.json')->assertNotFound();
    $this->get('/.well-known/mcp')->assertNotFound();
    $this->get('/.well-known/mcp/server-card.json')->assertNotFound();
});

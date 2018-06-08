# !/bin/bash
gcc teste_hosts.c -o teste_hosts.out
gcc teste_service.c -o teste_service.out
./teste_service.out
./teste_hosts.out
chmod +x testes*

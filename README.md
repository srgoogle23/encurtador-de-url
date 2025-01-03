# Encurtador de URL

Este projeto é um Encurtador de URL desenvolvido utilizando o framework [Hyperf](https://hyperf.io/). Ele oferece uma API para encurtar URLs longas e redirecionar para elas, facilitando o compartilhamento de links.

## Funcionalidades

-   Encurtar URLs longas.
-   Cadastro de URLs por usuários.
-   Redirecionamento para a URL original de maneira rápida e cacheada usando Redis.

## Requisitos

-   Docker e Docker Compose instalados na máquina.

## Configuração e Instalação

1. **Clone o repositório:**

    ```bash
    git clone https://github.com/srgoogle23/encurtador-de-url.git
    ```

2. **Navegue até o diretório do projeto:**

    ```bash
    cd encurtador-de-url
    ```

3. **Configure as variáveis de ambiente:**

    - Renomeie o arquivo `.env.example` para `.env`.
    - Atualize as configurações, como informações de banco de dados, conforme necessário.

4. **Inicie os containers com Docker Compose:**

    ```bash
    docker-compose up -d
    ```

5. **Execute as migrações para configurar o banco de dados:**

    ```bash
    docker-compose exec encurtador php bin/hyperf.php migrate
    ```

## Uso

Creating...

## Desenvolvimento e Contribuição

1. Faça um fork deste repositório.
2. Crie uma nova branch para sua feature ou correção: `git checkout -b minha-feature`.
3. Commit suas alterações: `git commit -m 'Adiciona minha feature'`.
4. Envie para o repositório remoto: `git push origin minha-feature`.
5. Abra um Pull Request.

## Licença

Este projeto está licenciado sob a Licença MIT. Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.

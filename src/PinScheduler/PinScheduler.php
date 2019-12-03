<?php

namespace PinScheduler;

class PinScheduler
{
    protected $defaultBoardId;

    protected $defaultBoardName;

    private $login;

    private $password;

    private $client;

    private $loggedIn = false;

    public function __construct($login, $password, $baseURI, $defaultBoardId)
    {
        $this->login = $login;
        $this->password = $password;
        $this->defaultBoardId = $defaultBoardId;
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $baseURI,
            'cookies' => true,
            'debug' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            ],

        ]);
    }

    public function addDrafts($drafts)
    {
        $this->loginFirstTime();

        $items = collect($drafts)->map(function (Draft $draft) {
            return $draft->toApiObject();
        });

        $this->client->post('/dashboard/publisher/draft-posts', [
            'form_params' => [
                'data' => json_encode((object) ['items' => $items], JSON_UNESCAPED_SLASHES),
            ],
            'headers' => [
                'Origin' => 'https://findtattoodesign.net',
                'Upgrade-Insecure-Requests' => '1',
                'Referer' => 'https://findtattoodesign.net/',
            ],
        ]);
    }

    public function publishDrafts()
    {
        $this->loginFirstTime();

        $response = $this->client->get('/dashboard/publisher/queue/posts/drafts', [
            'headers' => [
                'Origin' => 'https://findtattoodesign.net',
                'Upgrade-Insecure-Requests' => '1',
                'Referer' => 'https://findtattoodesign.net/',
            ],
        ]);

        $html = $response->getBody()->getContents();

        if (! preg_match('/({"posts":.+);\s*<\/script>/', $html, $matches)) {
            throw new \DomainException('Cannot find posts data on the page');
        }

        $drafts = collect(json_decode($matches[1])->posts)->values();

        foreach ($drafts as $draft) {
            $this->publishDraft($draft);
        }
    }

    private function loginFirstTime()
    {
        if (! $this->loggedIn) {
            $this->login();
        }
    }

    private function login()
    {
        $this->client->post('/login/check', [
            'form_params' => [
                'email' => $this->login,
                'password' => $this->password,
            ],
            'headers' => [
                'Upgrade-Insecure-Requests' => '1',
                'Referer' => 'https://www.tailwindapp.com/login',
            ],
        ]);

        $this->loggedIn = true;
    }

    private function publishDraft($draft)
    {
        if (preg_match('/ {(\d+?)}$/', $draft->description, $matches)) {
            $boardId = $matches[1];
            $draft->description = preg_replace('/ {(\d+?)}$/', '', $draft->description);
        } else {
            $boardId = $this->defaultBoardId;
        }

        if (preg_match('/ ~([\w ]+?)~/', $draft->description, $matches)) {
            $title = $matches[1];
            $draft->description = preg_replace('/ ~([\w ]+?)~/', '', $draft->description);
        } else {
            $title = 'Test Title';
        }

        $response = $this->client->post('/dashboard/publisher/post/schedule', [
            'form_params' => [
                'id' => $draft->id,
                'image_url' => $draft->image_url,
                'description' => $draft->description,
                'link' => $draft->link,
                'board[]' => $boardId,
                'title' => $title,

                'attribution' => '0',
                'button_classes' => 'btn btn-mini btn-success btn-schedule-post pull-right has-spinner',
                'is_popup' => '0',
                'schedule_type' => 'auto',
                'share_facebook' => '0',
                'share_to_facebook_page' => '0',
                'share_twitter' => '0',
                'total' => '1',
            ],
            'headers' => [
                'Origin' => 'https://www.tailwindapp.com',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Referer' => 'https://www.tailwindapp.com/dashboard/publisher/queue/posts/drafts',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
        ]);

        $contents = $response->getBody()->getContents();

        $success = json_decode($contents)->success;

        if (! $success) {
            throw new \DomainException("Could not publish draft: $contents");
        }
    }
}
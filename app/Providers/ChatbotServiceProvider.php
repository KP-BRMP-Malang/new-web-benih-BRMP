<?php

namespace App\Providers;

use App\Contracts\LlmClientInterface;
use App\Contracts\RetrievalServiceInterface;
use App\Services\Chatbot\ChatComposerService;
use App\Services\Chatbot\ChatbotOrchestratorService;
use App\Services\Chatbot\ChatRouterService;
use App\Services\Chatbot\PromptRepository;
use App\Services\Chatbot\RetrievalService;
use App\Services\LLM\GeminiClient;
use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind LLM Client interface to Gemini implementation
        $this->app->singleton(LlmClientInterface::class, function ($app) {
            return new GeminiClient();
        });

        // Bind Retrieval interface to default implementation
        $this->app->singleton(RetrievalServiceInterface::class, function ($app) {
            return new RetrievalService();
        });

        // Register PromptRepository as singleton
        $this->app->singleton(PromptRepository::class, function ($app) {
            return new PromptRepository();
        });

        // Register Router Service with PromptRepository dependency
        $this->app->singleton(ChatRouterService::class, function ($app) {
            return new ChatRouterService(
                $app->make(LlmClientInterface::class),
                $app->make(PromptRepository::class)
            );
        });

        // Register Composer Service
        $this->app->singleton(ChatComposerService::class, function ($app) {
            return new ChatComposerService(
                $app->make(LlmClientInterface::class)
            );
        });

        // Register Orchestrator Service
        $this->app->singleton(ChatbotOrchestratorService::class, function ($app) {
            return new ChatbotOrchestratorService(
                $app->make(ChatRouterService::class),
                $app->make(RetrievalServiceInterface::class),
                $app->make(ChatComposerService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

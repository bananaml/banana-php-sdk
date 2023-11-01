<?php

namespace BananaDev\Contracts;

use BananaDev\Responses\APIProjectResponse;
use BananaDev\Responses\APIProjectsResponse;

interface APIContract
{
    public function listProjects(array $query = []): APIProjectsResponse;

    public function getProject(string $projectId, array $query = []): APIProjectResponse;

    public function updateProject(string $projectId, array $data): APIProjectResponse;
}

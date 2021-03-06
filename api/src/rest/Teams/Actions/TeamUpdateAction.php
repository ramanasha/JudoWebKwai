<?php

namespace REST\Teams\Actions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Aura\Payload\Payload;

use Core\Responders\Responder;
use Core\Responders\JSONResponder;
use Core\Responders\JSONErrorResponder;
use Core\Responders\HTTPCodeResponder;
use Core\Responders\NotFoundResponder;

class TeamUpdateAction implements \Core\ActionInterface
{
    public function __invoke(RequestInterface $request, Payload $payload) : ResponseInterface
    {
        $id = $request->getAttribute('route.id');

        $teamsTable = \Domain\Team\TeamsTable::getTableFromRegistry();
        try {
            $team = $teamsTable->get($id);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $rnfe) {
            return (
                new NotFoundResponder(
                    new Responder(),
                    _("Team doesn't exist.")
                ))->respond();
        }

        $data = $payload->getInput();

        $validator = new \REST\Teams\TeamTypeValidator();
        $errors = $validator->validate($data);
        if (count($errors) > 0) {
            return (
                new JSONErrorResponder(
                    new HTTPCodeResponder(
                        new Responder(),
                        422
                    ),
                    $errors
                )
            )->respond();
        }

        $seasonId = \JmesPath\search('data.relationships.season.data.id', $data);
        if (isset($seasonId)) {
            try {
                $season = $teamsTable->Season->get($seasonId);
            } catch (\Cake\Datasource\Exception\RecordNotFoundException $rnfe) {
                return (
                    new JSONErrorResponder(
                        new HTTPCodeResponder(
                            new Responder(),
                            422
                        ),
                        [
                            '/data/relationships/season' => [
                                _('Season doesn\'t exist')
                            ]
                        ]
                    ))->respond();
            }
        }

        $teamTypeId = \JmesPath\search('data.relationships.team_type.data.id', $data);
        if (isset($teamTypeId)) {
            try {
                $team_type = $teamsTable->TeamType->get($teamTypeId);
            } catch (\Cake\Datasource\Exception\RecordNotFoundException $rnfe) {
                return (
                    new JSONErrorResponder(
                        new HTTPCodeResponder(
                            new Responder(),
                            422
                        ),
                        [
                            '/data/relationships/team_type' => [
                                _('Teamtype doesn\'t exist')
                            ]
                        ]
                    ))->respond();
            }
        }

        $attributes = \JmesPath\search('data.attributes', $data);

        if (isset($attributes['name'])) {
            $team->name = $attributes['name'];
        }
        if (isset($season)) {
            $team->season = $season;
        }
        if (isset($team_type)) {
            $team->team_type = $team_type;
        }
        if (isset($attributes['active'])) {
            $team->active = $attributes['active'];
        }
        if (isset($attributes['remark'])) {
            $team->remark = $attributes['remark'];
        }

        $teamsTable->save($team);

        $payload->setOutput(\Domain\Team\TeamTransformer::createForItem($team));

        return (
            new JSONResponder(
                new HTTPCodeResponder(
                    new Responder(),
                    201
                ),
                $payload
            ))->respond();
    }
}

<?php

namespace Desk\Model;

use Desk\Client;

interface HasRelationships
{

    /**
     * Sets the client that created this model
     *
     * @param Desk\Client $client The client that created this model
     */
    public function setClient(Client $client);

    /**
     * Gets the client that created this model
     *
     * @return Desk\Client
     */
    public function getClient();

    /**
     * Sets related models that were linked to from the API response
     *
     * @param array $links The "_links" node from the Desk response
     *
     * @return Desk\Model\HasRelationships $this
     * @chainable
     */
    public function setLinks(array $links);

    /**
     * Sets related models that were embedded in the API response
     *
     * @param array $embeds The "_embedded" node from the response
     *
     * @return Desk\Model\HasRelationships $this
     * @chainable
     */
    public function setEmbeds(array $embeds);

    /**
     * Gets a command representing one of this model's links
     *
     * @param string $linkName The name of the link (e.g. "self")
     *
     * @return Desk\Command\AbstractCommand
     */
    public function getLink($linkName);

    /**
     * Gets a model representing one of this model's embedded links
     *
     * @param string $embedName The name of the embedded link
     *
     * @return Desk\Command\AbstractCommand
     */
    public function getEmbed($embedName);
}

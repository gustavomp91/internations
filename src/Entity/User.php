<?php
	namespace App\Entity;
	use Doctrine\ORM\Mapping as ORM;
	use Doctrine\Common\Collections\ArrayCollection;
	use JMS\Serializer\Annotation as JMS;
	use App\Entity\Group;
	use Exception;

	/**
	* @ORM\Entity
	* @ORM\Table(name="users")
	*/

	class User {

		/**
		* @ORM\Id
		* @ORM\Column(type="integer")
		* @ORM\GeneratedValue(strategy="AUTO")
		*/
		private $id;
		/**
		* @ORM\Column(type="string", length=150)
		*/
		private $name;
		/**
		* @ORM\Column(type="boolean")
		*/
		private $active = 1;
		/**
		* @ORM\Column(type="datetime", nullable=true)
		*/
		private $delete_date = null;
		/**
		 * @ORM\ManyToMany(targetEntity="Group", mappedBy="users")
     	 * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
		 */
		private $groups;

		public function __construct() {
			$this->groups = new ArrayCollection();
		}

		public function setId($id) {
			$this->id = $id;
		}

		public function getId() {
			return $this->id;
		}

		public function setName($name) {
			$this->name = $name;	
		}

		public function getName() {
			return $this->name;	
		}

		public function setActive($active) {
			$this->active = $active;
		}

		public function getActive() {
			return $this->active;
		}

		public function setDeleteDate($delete_date) {
			$this->delete_date = $delete_date;
		}

		public function getDeleteDate() {
			return $this->delete_date;		
		}

		public function addGroup(Group $group) {
			if($this->groups->contains($group) === true) {
				return;
			}

			$this->groups->add($group);
			$group->addUser($this);
		}

		public function removeGroup(Group $group) {
			if($this->groups->contains($group) === false) {
				return;
			}

			$this->groups->removeElement($group);
		}

		public function getGroup() {
			return $this->groups;
		}

		public function setGroups($groups) {
			$this->groups = $group;

			return $this;
		}
	}
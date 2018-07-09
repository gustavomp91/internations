<?php
	namespace App\Controller;

	use DateTime;
	use Exception;
	use Symfony\Component\Form\Extension\Core\Type\HiddenType;
	use Symfony\Component\Routing\Annotation\Route;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpFoundation\JsonResponse;
	use Symfony\Component\Serializer\Serializer;
	use FOS\RestBundle\Controller\Annotations as FOSRest;
	use Symfony\Component\Form\Extension\Core\Type\DateType;
	use App\Entity\Group;
	use App\Entity\User;

	/**
	* Group controller
	* @Route("/api")
	*/

	class GroupController extends Controller {
		/**
		* Lists all groups
		* @FOSRest\Get("/group")
		*
		* @return json
		*/
		public function getGroupAction() {
			try {
				$serializer = $this->container->get('jms_serializer');
				$repository = $this->getDoctrine()->getRepository(Group::class);
				$group = $repository->findall();

				$response['Response'] = Response::HTTP_OK;
				$response['Message'] = $serializer->serialize($group, 'json');

				return new JsonResponse($response);
			} catch(Exception $ex) {
				$response['Response'] = Response::HTTP_INTERNAL_SERVER_ERROR;
				$response['Message'] = "There was an error: " . $ex->getMessage();
				return new JsonResponse($response);
			}
		}

		/**
		* Create group.
		* @FOSRest\Post("/group")
		*
		* @return json
		*/
		public function postGroupAction(Request $request) {
			try {
				$group = new Group();
				$group->setName($request->get('name'));
				$em = $this->getDoctrine()->getManager();
				$em->persist($group);
				$em->flush();

				$response['Response'] = Response::HTTP_CREATED;
				$response['Message'] = "Group inserted. Id: " . $group->getId();

				return new JsonResponse($response);
			} catch(Exception $ex) {
				$response['Response'] = Response::HTTP_INTERNAL_SERVER_ERROR;
				$response['Message'] = "There was an error: " . $ex->getMessage();
				return new JsonResponse($response);
			}
		}

		/**
		* Delete group.
		* @FOSRest\Delete("/group/delete/{id}")
		*
		* @return json
		*/
		public function deleteGroupAction($id) {
			try {
				$em = $this->getDoctrine()->getManager();
				$group = $em->getRepository(Group::class)->find($id);

				if(!$group) {
					throw new Exception('No group found for ID ' . $id);
				}

				if(count($group->getUser()) > 0) {
					throw new Exception('You have to delete all users from this group in order to delete it.');	
				}

				if(!$group->getActive()) {
					throw new Exception('Group ' . $group->getName() . ' has already been deleted.');
				}

				$group->setActive(0);
				$group->setDeleteDate(new DateTime());
				$em->flush();

				$response['Response'] = Response::HTTP_OK;
				$response['Message'] = "Group deleted. Id: " . $id;

				return new JsonResponse($response);
			} catch(Exception $ex) {
				$response['Response'] = Response::HTTP_INTERNAL_SERVER_ERROR;
				$response['Message'] = "There was an error: " . $ex->getMessage();
				return new JsonResponse($response);
			}
		}

		/**
		* Add user to group
		* @FOSRest\Post("/group/adduser")
		* @return json
		*/
		public function postAddUserToGroupAction(Request $request) {
			try {
				$groupid = $request->get('groupid');
				$userid = $request->get('userid');

				$group = $this->getDoctrine()->getRepository(Group::class)->findOneById($groupid);

				if(empty($group)) {
					throw new Exception("Cant find group " . $groupid);
				}

				if(!$group->getActive()) {
					throw new Exception("You cant add users to a inactive group.");
				}

				$user = $this->getDoctrine()->getRepository(User::class)->findOneById($userid);

				if(empty($user)) {
					throw new Exception("Cant find user " . $userid);
				}

				if(!$user->getActive()) {
					throw new Exception("You cant add inactive users to a group.");
				}

				$result = $group->addUser($user);

				$this->getDoctrine()->getManager()->persist($group);

				$this->getDoctrine()->getManager()->flush();

				$response['Response'] = Response::HTTP_OK;

				if($result) {
					$response['Message'] = "User Id " . $user->getName() . " added to group Id " . $group->getName();
				} else {
					$response['Message'] =  "User " . $user->getName() . " already is part of group " . $group->getName();
				}

				return new JsonResponse($response);
			} catch(Exception $ex) {
				$response['Response'] = Response::HTTP_INTERNAL_SERVER_ERROR;
				$response['Message'] = "There was an error: " . $ex->getMessage();
				return new JsonResponse($response);
			}
		}

		/**
		* Remove user from group
		* @FOSRest\Delete("/group/removeuser/{groupid}/{userid}")
		* @return json
		*/
		public function deleteRemoveUserFromGroupAction(Request $request) {
			try {
				$groupid = $request->get('groupid');
				$userid = $request->get('userid');

				$group = $this->getDoctrine()->getRepository(Group::class)->findOneById($groupid);

				if(empty($group)) {
					throw new Exception("Cant find group " . $groupid);
				}

				$user = $this->getDoctrine()->getRepository(User::class)->findOneById($userid);

				if(empty($user)) {
					throw new Exception("Cant find user " . $userid);
				}

				$result = $group->removeUser($user);

				$this->getDoctrine()->getManager()->persist($group);

				$this->getDoctrine()->getManager()->flush();

				$response['Response'] = Response::HTTP_OK;

				if($result) {
					$response['Message'] = "User Id " . $user->getName() . " removed from group Id " . $group->getName();
				} else {
					$response['Message'] = 'User ' . $user->getName() . ' has already been deleted from group ' . $group->getName();
				}
				

				return new JsonResponse($response);
			} catch(Exception $ex) {
				$response['Response'] = Response::HTTP_INTERNAL_SERVER_ERROR;
				$response['Message'] = "There was an error: " . $ex->getMessage();
				return new JsonResponse($response);
			}
		}
	}
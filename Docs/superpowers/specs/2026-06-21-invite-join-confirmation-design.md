# Invite Join Confirmation Design

## Goal

Make group invite links usable as normal shared links while keeping membership changes explicit. A user who opens an invite link should see a confirmation page before joining the group.

## Current Behavior

Groups have an `invite_token`. Group admins can see an invite URL on the group detail page. The current join route is `POST /groups/{group}/join/{token}`, so opening the invite URL directly in a browser does not provide a natural join flow.

## Proposed Behavior

Add a `GET /groups/{group}/join/{token}` route that renders a join confirmation page. The route uses the same authenticated user constraints as the existing group routes: `auth`, `active`, and `verified`.

If a guest opens the invite URL, Laravel redirects them to login. After login, Laravel returns them to the original invite URL, where they see the confirmation page.

The confirmation page shows the group name, description, status, and member count. It includes a `Join group` button that submits to the existing `POST /groups/{group}/join/{token}` route.

If the user is already a member, the page shows an already-joined state and a link to the group detail page instead of a join button.

Invalid invite tokens return `404` for both the preview and join submission.

## Backend Design

Add a new controller method on `GroupController`, tentatively named `joinPreview(Request $request, Group $group, string $token): Response`.

The method validates the invite token with `hash_equals($group->invite_token, $token)`. It loads a member count and checks whether the current user is already a member. It renders `groups/Join` with the group summary, invite token or post URL, and membership state.

Keep the existing `join` method as the only method that changes membership. It continues to use `syncWithoutDetaching` so repeated submissions remain safe.

## Frontend Design

Create `resources/js/pages/groups/Join.vue`.

The page uses the default `AppLayout` because its page name starts with `groups/`. It presents a clear confirmation screen with two states:

- Not a member: show group summary and a `Join group` button.
- Already a member: show group summary and a link to open the group.

The join button submits a POST request through Inertia to the existing join route.

## Error Handling

Invalid tokens return `404` instead of revealing whether a group exists. Guest users go through the existing auth redirect. Inactive or unverified users are blocked by the same middleware already used by the product routes.

## Testing

Add feature coverage for:

- Authenticated users can view the invite confirmation page with a valid token.
- Guests opening a valid invite confirmation URL are redirected to login.
- Invalid tokens return `404` on the confirmation page.
- Already-member users see the confirmation page successfully without duplicate membership changes.
- Posting the join form still attaches the user as a member and redirects to the group detail page.
